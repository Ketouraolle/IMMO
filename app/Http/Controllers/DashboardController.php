<?php

namespace App\Http\Controllers;

use App\Models\Issue;
use App\Models\Payment;
use App\Models\Property;
use App\Models\VisitRequest;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        if ($user->isAdmin()) {
            $approvedThisMonth = Payment::where('status', 'approved')->whereMonth('paid_on', now()->month)->whereYear('paid_on', now()->year);

            return view('dashboard.admin', [
                'propertyCount' => Property::count(),
                'occupiedCount' => Property::where('status', 'occupied')->count(),
                'vacantCount' => Property::where('status', 'vacant')->count(),
                'openIssues' => Issue::whereIn('status', ['open', 'in_progress'])->count(),
                'pendingPayments' => Payment::where('status', 'pending')->count(),
                'thisMonthCollected' => (clone $approvedThisMonth)->sum('amount'),
                'commissionThisMonth' => (clone $approvedThisMonth)->sum('commission_amount'),
                'newVisitRequests' => VisitRequest::where('status', 'new')->count(),
                'visitFeesThisMonth' => VisitRequest::where('payment_status', 'paid')->whereMonth('paid_at', now()->month)->whereYear('paid_at', now()->year)->sum('fee_amount'),
                'upcomingVisits' => VisitRequest::with('property')->whereIn('status', ['new', 'confirmed'])->whereDate('visit_date', '>=', today())
                    ->orderBy('visit_date')->orderBy('visit_time')->take(6)->get(),
                'recentPayments' => Payment::with(['lease.property', 'lease.tenant'])->where('status', 'approved')->latest('paid_on')->take(6)->get(),
                'recentIssues' => Issue::with('property')->latest()->take(6)->get(),
            ]);
        }

        if ($user->isOwner()) {
            $propertyIds = $user->properties()->pluck('id');
            $approved = fn ($q) => $q->where('payments.status', 'approved');
            $approvedThisMonth = Payment::where('status', 'approved')->whereHas('lease', fn ($q) => $q->whereIn('property_id', $propertyIds))
                ->whereMonth('paid_on', now()->month)->whereYear('paid_on', now()->year);

            return view('dashboard.owner', [
                'properties' => $user->properties()
                    ->withSum(['payments as total_collected' => $approved], 'amount')
                    ->withSum(['payments as total_commission' => $approved], 'commission_amount')
                    ->with('activeLease.tenant')
                    ->orderBy('name')
                    ->get(),
                'propertyCount' => $propertyIds->count(),
                'occupiedCount' => Property::where('owner_id', $user->id)->where('status', 'occupied')->count(),
                'vacantCount' => Property::where('owner_id', $user->id)->where('status', 'vacant')->count(),
                'thisMonthCollected' => (clone $approvedThisMonth)->sum('amount'),
                'commissionThisMonth' => (clone $approvedThisMonth)->sum('commission_amount'),
                'openIssues' => Issue::whereIn('property_id', $propertyIds)->whereIn('status', ['open', 'in_progress'])->count(),
                'recentPayments' => Payment::with(['lease.property'])->where('status', 'approved')->whereHas('lease', fn ($q) => $q->whereIn('property_id', $propertyIds))->latest('paid_on')->take(6)->get(),
                'recentIssues' => Issue::with('property')->whereIn('property_id', $propertyIds)->latest()->take(6)->get(),
            ]);
        }

        // tenant
        $lease = $user->leases()->with('property', 'contract')->where('status', 'active')->latest()->first();
        $contract = $lease?->contract?->isDraft() ? null : $lease?->contract;

        return view('dashboard.tenant', [
            'lease' => $lease,
            'contract' => $contract,
            'payments' => $lease ? $lease->payments()->latest('paid_on')->get() : collect(),
            'issues' => $user->reportedIssues()->with('property')->latest()->get(),
        ]);
    }
}
