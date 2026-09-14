<?php

namespace App\Http\Controllers;

use App\Models\Lease;
use App\Models\Payment;
use App\Models\Property;
use App\Models\User;
use App\Services\MobileMoneySimulator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class PaymentController extends Controller
{
    public const STATUSES = ['pending', 'approved', 'rejected'];

    // Payment history: everyone sees what they're allowed to, with filters, totals and a summary
    public function index(Request $request)
    {
        $user = $request->user();
        $filters = $this->filters($request);
        $query = $this->filteredQuery($user, $filters);

        $payments = (clone $query)
            ->with(['lease.property', 'lease.tenant'])
            ->latest('paid_on')
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        // Totals cover every page of the filtered result, approved payments only
        $totals = (clone $query)
            ->where('payments.status', 'approved')
            ->toBase()
            ->selectRaw('COUNT(*) as count, COALESCE(SUM(amount), 0) as amount, COALESCE(SUM(commission_amount), 0) as commission')
            ->first();

        return view('payments.index', [
            'payments' => $payments,
            'totals' => $totals,
            'filters' => $filters,
            'hasFilters' => collect($filters)->except('status')->filter()->isNotEmpty(),
            'stats' => $this->stats($user),
            'lease' => $user->isTenant() ? $user->leases()->with('property')->where('status', 'active')->latest()->first() : null,
            'properties' => $user->isTenant()
                ? collect()
                : Property::query()->when($user->isOwner(), fn ($q) => $q->where('owner_id', $user->id))->orderBy('name')->get(['id', 'name']),
        ]);
    }

    // The same filtered history as a spreadsheet
    public function export(Request $request)
    {
        $user = $request->user();
        $showCommission = ! $user->isTenant();

        $payments = $this->filteredQuery($user, $this->filters($request))
            ->with(['lease.property', 'lease.tenant'])
            ->latest('paid_on')
            ->latest('id')
            ->get();

        $headers = [__('Receipt'), __('Date'), __('Tenant'), __('Property'), __('Period covered'), __('Method'), __('Reference'), __('Status'), __('Amount (XAF)')];
        if ($showCommission) {
            array_push($headers, __('Commission (XAF)'), __('Net to owner (XAF)'));
        }

        return response()->streamDownload(function () use ($payments, $headers, $showCommission) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF"); // BOM so spreadsheet apps read accents correctly

            fputcsv($out, $headers, ',', '"', '');
            foreach ($payments as $p) {
                $row = [
                    $p->receipt_number, $p->paid_on->toDateString(), $p->lease->tenant->name, $p->lease->property->name,
                    $p->period_covered, $p->methodLabel(), $p->transaction_ref, __(ucfirst($p->status)), (float) $p->amount,
                ];
                if ($showCommission) {
                    array_push($row, $p->isApproved() ? (float) $p->commission_amount : '', $p->isApproved() ? $p->netAmount() : '');
                }
                fputcsv($out, array_map($this->csvCell(...), $row), ',', '"', '');
            }

            fclose($out);
        }, 'payments-'.now()->format('Y-m-d').'.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
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

        return redirect()->route('payments.receipt', $payment)->with('status', __('Payment recorded.'));
    }

    // Tenant paying rent through the (simulated) Orange Money / MTN MoMo checkout.
    public function submitForm(Request $request)
    {
        $user = $request->user();
        abort_unless($user->isTenant(), 403);

        $lease = $user->leases()->with('property')->where('status', 'active')->latest()->first();
        abort_unless($lease, 404, __("You don't have an active lease."));

        return view('payments.submit', compact('lease'));
    }

    public function submit(Request $request, MobileMoneySimulator $simulator)
    {
        $user = $request->user();
        abort_unless($user->isTenant(), 403);

        $lease = $user->leases()->where('status', 'active')->latest()->first();
        abort_unless($lease, 404, __("You don't have an active lease."));

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

        return redirect()->route('payments.receipt', $payment)->with('status', __('Payment confirmed — your receipt is ready.'));
    }

    // Admin validates a tenant-submitted payment: generates the receipt.
    public function approve(Request $request, Payment $payment)
    {
        abort_unless($request->user()->isAdmin(), 403);
        abort_unless($payment->isPending(), 400, __('This payment has already been reviewed.'));

        $payment->markApproved($request->user());

        return back()->with('status', __('Payment approved and receipt generated.'));
    }

    public function reject(Request $request, Payment $payment)
    {
        abort_unless($request->user()->isAdmin(), 403);
        abort_unless($payment->isPending(), 400, __('This payment has already been reviewed.'));

        $data = $request->validate([
            'rejection_reason' => ['required', 'string', 'max:500'],
        ]);

        $payment->update([
            'status' => 'rejected',
            'recorded_by' => $request->user()->id,
            'rejection_reason' => $data['rejection_reason'],
        ]);

        return back()->with('status', __('Payment rejected.'));
    }

    public function receipt(Request $request, Payment $payment)
    {
        $user = $request->user();
        abort_unless($payment->isApproved(), 404, __('No receipt available for this payment yet.'));

        $payment->load('lease.property', 'lease.tenant', 'recordedBy');

        if ($user->isOwner() && $payment->lease->property->owner_id !== $user->id) {
            abort(403);
        }
        if ($user->isTenant() && $payment->lease->tenant_id !== $user->id) {
            abort(403);
        }

        return view('payments.receipt', compact('payment'));
    }

    /**
     * Read filters from the query string. Invalid values are ignored rather than redirecting,
     * so a hand-edited URL never bounces the user around.
     *
     * @return array{status: ?string, method: ?string, property: ?int, from: ?string, to: ?string, q: ?string}
     */
    private function filters(Request $request): array
    {
        $date = function (mixed $value): ?string {
            if (! is_string($value) || ! preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
                return null;
            }

            return rescue(fn () => Carbon::createFromFormat('Y-m-d', $value)->toDateString(), null, false);
        };

        $from = $date($request->query('from'));
        $to = $date($request->query('to'));
        if ($from && $to && $to < $from) {
            [$from, $to] = [$to, $from];
        }

        $status = $request->query('status');
        $method = $request->query('method');
        $property = $request->query('property');
        $search = trim((string) $request->query('q', ''));

        return [
            'status' => in_array($status, self::STATUSES, true) ? $status : null,
            'method' => is_string($method) && array_key_exists($method, Payment::METHODS) ? $method : null,
            'property' => is_string($property) && ctype_digit($property) ? (int) $property : null,
            'from' => $from,
            'to' => $to,
            'q' => $search !== '' ? Str::limit($search, 100, '') : null,
        ];
    }

    /** Payments the user may see. */
    private function scopedQuery(User $user): Builder
    {
        return Payment::query()
            ->when($user->isOwner(), fn ($q) => $q->whereHas('lease.property', fn ($p) => $p->where('owner_id', $user->id)))
            ->when($user->isTenant(), fn ($q) => $q->whereHas('lease', fn ($l) => $l->where('tenant_id', $user->id)));
    }

    private function filteredQuery(User $user, array $filters): Builder
    {
        return $this->scopedQuery($user)
            ->when($filters['status'], fn ($q, $status) => $q->where('payments.status', $status))
            ->when($filters['method'], fn ($q, $method) => $q->where('payments.method', $method))
            ->when($filters['property'], fn ($q, $id) => $q->whereHas('lease', fn ($l) => $l->where('property_id', $id)))
            ->when($filters['from'], fn ($q, $from) => $q->whereDate('paid_on', '>=', $from))
            ->when($filters['to'], fn ($q, $to) => $q->whereDate('paid_on', '<=', $to))
            ->when($filters['q'], fn ($q, $term) => $q->where(fn ($w) => $w
                ->whereLike('receipt_number', "%{$term}%")
                ->orWhereLike('transaction_ref', "%{$term}%")
                ->orWhereLike('period_covered', "%{$term}%")
                ->orWhereHas('lease.tenant', fn ($t) => $t->whereLike('name', "%{$term}%"))
                ->orWhereHas('lease.property', fn ($p) => $p->whereLike('name', "%{$term}%"))));
    }

    /** Summary cards above the history (not affected by filters). */
    private function stats(User $user): array
    {
        $approved = fn () => $this->scopedQuery($user)->where('payments.status', 'approved');
        $thisMonth = fn () => $approved()->whereYear('paid_on', now()->year)->whereMonth('paid_on', now()->month);
        $thisYear = fn () => $approved()->whereYear('paid_on', now()->year);
        $pending = fn () => $this->scopedQuery($user)->where('payments.status', 'pending');

        return [
            'monthAmount' => (float) $thisMonth()->sum('amount'),
            'monthCommission' => (float) $thisMonth()->sum('commission_amount'),
            'yearAmount' => (float) $thisYear()->sum('amount'),
            'yearCommission' => (float) $thisYear()->sum('commission_amount'),
            'pendingCount' => $pending()->count(),
            'pendingAmount' => (float) $pending()->sum('amount'),
            'lastPayment' => $approved()->latest('paid_on')->latest('id')->first(),
        ];
    }

    // Stop spreadsheet apps from treating a cell as a formula
    private function csvCell(mixed $value): mixed
    {
        return is_string($value) && $value !== '' && in_array($value[0], ['=', '+', '-', '@', "\t", "\r"], true) ? "'".$value : $value;
    }
}
