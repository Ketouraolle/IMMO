<?php

namespace App\Http\Controllers;

use App\Models\Issue;
use App\Models\Lease;
use App\Models\Payment;
use App\Models\Property;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        if ($user->isAdmin()) {
            return view('dashboard.admin', [
                'propertyCount' => Property::count(),
                'occupiedCount' => Property::where('status', 'occupied')->count(),
                'vacantCount' => Property::where('status', 'vacant')->count(),
                'openIssues' => Issue::whereIn('status', ['open', 'in_progress'])->count(),
                'pendingPayments' => Payment::where('status', 'pending')->count(),
                'thisMonthCollected' => Payment::where('status', 'approved')->whereMonth('paid_on', now()->month)->whereYear('paid_on', now()->year)->sum('amount'),
                'recentPayments' => Payment::with(['lease.property', 'lease.tenant'])->where('status', 'approved')->latest('paid_on')->take(6)->get(),
                'recentIssues' => Issue::with('property')->latest()->take(6)->get(),
            ]);
        }

        if ($user->isOwner()) {
            $propertyIds = $user->properties()->pluck('id');

            return view('dashboard.owner', [
                'properties' => $user->properties()
                    ->withSum(['payments as total_collected' => fn ($q) => $q->where('status', 'approved')], 'amount')
                    ->with('activeLease.tenant')
                    ->orderBy('name')
                    ->get(),
                'propertyCount' => $propertyIds->count(),
                'occupiedCount' => Property::where('owner_id', $user->id)->where('status', 'occupied')->count(),
                'vacantCount' => Property::where('owner_id', $user->id)->where('status', 'vacant')->count(),
                'thisMonthCollected' => Payment::where('status', 'approved')->whereHas('lease', fn ($q) => $q->whereIn('property_id', $propertyIds))
                    ->whereMonth('paid_on', now()->month)->whereYear('paid_on', now()->year)->sum('amount'),
                'openIssues' => Issue::whereIn('property_id', $propertyIds)->whereIn('status', ['open', 'in_progress'])->count(),
                'recentPayments' => Payment::with(['lease.property'])->where('status', 'approved')->whereHas('lease.property', fn ($q) => $q->whereIn('property_id', $propertyIds))->latest('paid_on')->take(6)->get(),
                'recentIssues' => Issue::with('property')->whereIn('property_id', $propertyIds)->latest()->take(6)->get(),
            ]);
        }

        // tenant
        $lease = $user->leases()->with('property')->where('status', 'active')->latest()->first();

        return view('dashboard.tenant', [
            'lease' => $lease,
            'payments' => $lease ? $lease->payments()->latest('paid_on')->get() : collect(),
            'issues' => $user->reportedIssues()->with('property')->latest()->get(),
        ]);
    }
}
