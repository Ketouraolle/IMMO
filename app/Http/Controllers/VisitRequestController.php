<?php

namespace App\Http\Controllers;

use App\Models\VisitRequest;
use App\Services\MobileMoneySimulator;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

// Public booking lives in App\Livewire\VisitBooking; everything here is admin-only.
class VisitRequestController extends Controller
{
    public function index(Request $request)
    {
        abort_unless($request->user()->isAdmin(), 403);

        $visitRequests = VisitRequest::with('property')
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->input('status')))
            ->when($request->filled('payment'), fn ($q) => $q->where('payment_status', $request->input('payment')))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('visit-requests.index', compact('visitRequests'));
    }

    public function show(Request $request, VisitRequest $visitRequest)
    {
        abort_unless($request->user()->isAdmin(), 403);

        $visitRequest->load('property.mainImage', 'handledBy');

        return view('visit-requests.show', compact('visitRequest'));
    }

    public function updateStatus(Request $request, VisitRequest $visitRequest)
    {
        abort_unless($request->user()->isAdmin(), 403);

        $data = $request->validate([
            'status' => ['required', Rule::in(array_keys(VisitRequest::STATUSES))],
        ]);

        $data['handled_by'] = $request->user()->id;

        $visitRequest->update($data);

        return back()->with('status', 'Visit marked as '.strtolower(VisitRequest::STATUSES[$data['status']]).'.');
    }

    // Fee paid at the visit: cash is recorded as-is, mobile money goes through the simulated operator.
    public function collectFee(Request $request, VisitRequest $visitRequest, MobileMoneySimulator $simulator)
    {
        abort_unless($request->user()->isAdmin(), 403);
        abort_unless($visitRequest->needsPayment(), 400, 'There is no fee to collect on this visit.');

        $data = $request->validate([
            'method' => ['required', Rule::in(['cash', ...array_keys(MobileMoneySimulator::OPERATORS)])],
            'phone' => ['nullable', 'required_unless:method,cash', MobileMoneySimulator::PHONE_RULE],
        ]);

        $visitRequest->update([
            'payment_status' => 'paid',
            'payment_method' => $data['method'],
            'transaction_ref' => $data['method'] === 'cash'
                ? null
                : $simulator->charge($data['method'], $data['phone'], (float) $visitRequest->fee_amount),
            'paid_at' => now(),
            'handled_by' => $request->user()->id,
        ]);

        return back()->with('status', 'Visit fee of '.number_format($visitRequest->fee_amount).' XAF collected.');
    }
}
