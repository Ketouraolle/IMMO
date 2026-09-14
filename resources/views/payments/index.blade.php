@extends('layouts.app')
@section('title', 'Payments')
@section('content')
    @php
        $user = auth()->user();
        $showCommission = ! $user->isTenant();
        $tone = ['pending' => 'warning', 'approved' => 'success', 'rejected' => 'danger'];
        $cols = 7 + ($showCommission ? 2 : 0) + ($user->isTenant() ? 0 : 1);
    @endphp

    <div class="page-head">
        <div>
            <h3>{{ $user->isTenant() ? 'Payment history' : 'Payments' }}</h3>
            @if($user->isOwner())<p class="page-head__sub">Net amounts are after the agency commission.</p>@endif
        </div>
        @if($user->isTenant())
            <a href="{{ route('payments.submit-form') }}" class="btn btn-dark btn-sm"><i class="bi bi-phone"></i> Pay rent</a>
        @endif
    </div>

    <div class="pill-nav mb-3">
        <a href="{{ route('payments.index') }}" class="{{ request('status') ? '' : 'active' }}">All</a>
        @foreach(['pending' => 'Pending', 'approved' => 'Approved', 'rejected' => 'Rejected'] as $value => $label)
            <a href="{{ route('payments.index', ['status' => $value]) }}" class="{{ request('status') === $value ? 'active' : '' }}">{{ $label }}</a>
        @endforeach
    </div>

    <div class="card">
        <div class="table-responsive">
            <table class="table mb-0 align-middle">
                <thead>
                <tr>
                    <th>Receipt</th>
                    @unless($user->isTenant())<th>Tenant</th>@endunless
                    <th>Property</th>
                    <th class="text-end">Amount</th>
                    @if($showCommission)<th class="text-end">Commission</th><th class="text-end">Net to owner</th>@endif
                    <th>Date</th><th>Method</th><th>Status</th><th></th>
                </tr>
                </thead>
                <tbody>
                @forelse($payments as $p)
                    <tr>
                        <td class="small">{{ $p->receipt_number ?? '—' }}</td>
                        @unless($user->isTenant())<td>{{ $p->lease->tenant->name }}</td>@endunless
                        <td>{{ $p->lease->property->name }}</td>
                        <td class="text-end fw-semibold text-nowrap">{{ number_format($p->amount) }} XAF</td>
                        @if($showCommission)
                            <td class="text-end text-nowrap text-muted small">
                                @if($p->isApproved()){{ number_format($p->commission_amount) }} <span class="d-block" style="font-size:.7rem;">{{ rtrim(rtrim(number_format($p->commission_rate, 2), '0'), '.') }}%</span>@else — @endif
                            </td>
                            <td class="text-end text-nowrap">@if($p->isApproved()){{ number_format($p->netAmount()) }} XAF @else — @endif</td>
                        @endif
                        <td class="text-nowrap">{{ $p->paid_on->format('d M Y') }}</td>
                        <td>
                            {{ $p->methodLabel() }}
                            @if($p->transaction_ref)<div class="small text-muted font-monospace" style="font-size:.7rem;">{{ $p->transaction_ref }}</div>@endif
                        </td>
                        <td>
                            <span class="badge-soft badge-soft--{{ $tone[$p->status] }}">{{ ucfirst($p->status) }}</span>
                            @if($p->isRejected() && $p->rejection_reason)
                                <div class="small text-muted">{{ $p->rejection_reason }}</div>
                            @endif
                        </td>
                        <td class="text-end">
                            @if($p->isApproved())
                                <a href="{{ route('payments.receipt', $p) }}" class="btn btn-sm btn-outline-dark">Receipt</a>
                            @elseif($p->isPending() && $user->isAdmin())
                                <div class="d-flex gap-1 justify-content-end">
                                    <form method="POST" action="{{ route('payments.approve', $p) }}">
                                        @csrf
                                        <button class="btn btn-sm btn-success">Approve</button>
                                    </form>
                                    <button type="button" class="btn btn-sm btn-outline-danger" data-bs-toggle="collapse" data-bs-target="#reject-{{ $p->id }}">Reject</button>
                                </div>
                                <div class="collapse mt-2" id="reject-{{ $p->id }}">
                                    <form method="POST" action="{{ route('payments.reject', $p) }}" class="d-flex gap-1">
                                        @csrf
                                        <input type="text" name="rejection_reason" class="form-control form-control-sm" placeholder="Reason" required>
                                        <button class="btn btn-sm btn-danger">Confirm</button>
                                    </form>
                                </div>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="{{ $cols }}" class="text-muted text-center py-5">No payments recorded yet</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="mt-3">{{ $payments->links() }}</div>
@endsection
