<?php

namespace App\Http\Controllers;

use App\Models\Lease;
use App\Models\Payment;
use App\Services\MobileMoneySimulator;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PaymentController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $payments = Payment::with(['lease.property', 'lease.tenant'])
            ->when($user->isOwner(), fn ($q) => $q->whereHas('lease.property', fn ($p) => $p->where('owner_id', $user->id)))
            ->when($user->isTenant(), fn ($q) => $q->whereHas('lease', fn ($l) => $l->where('tenant_id', $user->id)))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->input('status')))
            ->latest('paid_on')
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        return view('payments.index', compact('payments'));
    }

    // Admin recording a payment directly on a tenant's behalf — auto-approved, receipt generated immediately.
    public function create(Request $request, Lease $lease)
    {
        abort_unless($request->user()->isAdmin(), 403);

        return view('payments.create', compact('lease'));
    }

    public function store(Request $request, Lease $lease)
    {
        abort_unless($request->user()->isAdmin(), 403);

        $data = $request->validate([
            'amount' => ['required', 'numeric', 'min:0'],
            'paid_on' => ['required', 'date'],
            'period_covered' => ['nullable', 'string', 'max:100'],
            'method' => ['required', Rule::in(array_keys(Payment::METHODS))],
            'transaction_ref' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string'],
        ]);

        $payment = $lease->payments()->create($data + ['status' => 'pending']);
        $payment->markApproved($request->user());

        return redirect()->route('payments.receipt', $payment)->with('status', 'Payment recorded.');
    }

    // Tenant paying rent through the (simulated) Orange Money / MTN MoMo checkout.
    public function submitForm(Request $request)
    {
        $user = $request->user();
        abort_unless($user->isTenant(), 403);

        $lease = $user->leases()->with('property')->where('status', 'active')->latest()->first();
        abort_unless($lease, 404, "You don't have an active lease.");

        return view('payments.submit', compact('lease'));
    }

    public function submit(Request $request, MobileMoneySimulator $simulator)
    {
        $user = $request->user();
        abort_unless($user->isTenant(), 403);

        $lease = $user->leases()->where('status', 'active')->latest()->first();
        abort_unless($lease, 404, "You don't have an active lease.");

        $data = $request->validate([
            'amount' => ['required', 'numeric', 'min:1'],
            'period_covered' => ['nullable', 'string', 'max:100'],
            'method' => ['required', Rule::in(array_keys(MobileMoneySimulator::OPERATORS))],
            'phone' => ['required', MobileMoneySimulator::PHONE_RULE],
        ]);

        // The operator confirmed the charge, so it counts as proof of payment.
        $payment = $lease->payments()->create([
            'submitted_by' => $user->id,
            'amount' => $data['amount'],
            'paid_on' => today(),
            'period_covered' => $data['period_covered'] ?? null,
            'method' => $data['method'],
            'transaction_ref' => $simulator->charge($data['method'], $data['phone'], (float) $data['amount']),
            'status' => 'pending',
        ]);
        $payment->markApproved(null);

        return redirect()->route('payments.receipt', $payment)->with('status', 'Payment confirmed — your receipt is ready.');
    }

    // Admin validates a tenant-submitted payment: generates the receipt.
    public function approve(Request $request, Payment $payment)
    {
        abort_unless($request->user()->isAdmin(), 403);
        abort_unless($payment->isPending(), 400, 'This payment has already been reviewed.');

        $payment->markApproved($request->user());

        return back()->with('status', 'Payment approved and receipt generated.');
    }

    public function reject(Request $request, Payment $payment)
    {
        abort_unless($request->user()->isAdmin(), 403);
        abort_unless($payment->isPending(), 400, 'This payment has already been reviewed.');

        $data = $request->validate([
            'rejection_reason' => ['required', 'string', 'max:500'],
        ]);

        $payment->update([
            'status' => 'rejected',
            'recorded_by' => $request->user()->id,
            'rejection_reason' => $data['rejection_reason'],
        ]);

        return back()->with('status', 'Payment rejected.');
    }

    public function receipt(Request $request, Payment $payment)
    {
        $user = $request->user();
        abort_unless($payment->isApproved(), 404, 'No receipt available for this payment yet.');

        $payment->load('lease.property', 'lease.tenant', 'recordedBy');

        if ($user->isOwner() && $payment->lease->property->owner_id !== $user->id) {
            abort(403);
        }
        if ($user->isTenant() && $payment->lease->tenant_id !== $user->id) {
            abort(403);
        }

        return view('payments.receipt', compact('payment'));
    }
}
