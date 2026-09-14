@extends('layouts.app')
@section('title', auth()->user()->isTenant() ? __('Payment history') : __('Payments'))
@section('content')
    @php
        $user = auth()->user();
        $showCommission = ! $user->isTenant();
        $tone = ['pending' => 'warning', 'approved' => 'success', 'rejected' => 'danger'];
        $cols = 6 + ($showCommission ? 2 : 0);
    @endphp

    <div class="page-head">
        <div>
            <h3>{{ $user->isTenant() ? __('Payment history') : __('Payments') }}</h3>
            @if($user->isOwner())<p class="page-head__sub">{{ __('Net amounts are after the agency commission.') }}</p>@endif
        </div>
        @if($user->isTenant())
            <a href="{{ route('payments.submit-form') }}" class="btn btn-dark btn-sm"><i class="bi bi-phone"></i> {{ __('Pay rent') }}</a>
        @endif
    </div>

    <div class="pill-nav mb-3">
        <a href="{{ route('payments.index') }}" class="{{ request('status') ? '' : 'active' }}">{{ __('All') }}</a>
        @foreach(['pending' => __('Pending'), 'approved' => __('Approved'), 'rejected' => __('Rejected')] as $value => $label)
            <a href="{{ route('payments.index', ['status' => $value]) }}" class="{{ request('status') === $value ? 'active' : '' }}">{{ $label }}</a>
        @endforeach
    </div>

    <div class="card">
        <div class="table-responsive">
            <table class="table mb-0 align-middle">
                <thead>
                <tr>
                    <th>{{ $user->isTenant() ? __('Property') : __('Tenant') }}</th>
                    <th class="text-end">{{ __('Amount') }}</th>
                    @if($showCommission)<th class="text-end">{{ __('Commission') }}</th><th class="text-end">{{ __('Net to owner') }}</th>@endif
                    <th>{{ __('Date') }}</th><th>{{ __('Method') }}</th><th>{{ __('Status') }}</th><th></th>
                </tr>
                </thead>
                <tbody>
                @forelse($payments as $p)
                    <tr>
                        <td>
                            @unless($user->isTenant())<div class="fw-semibold">{{ $p->lease->tenant->name }}</div>@endunless
                            <div class="{{ $user->isTenant() ? 'fw-semibold' : 'small text-muted' }}">{{ $p->lease->property->name }}</div>
                            @if($p->receipt_number)<div class="small text-muted font-monospace" style="font-size:.7rem;">{{ $p->receipt_number }}</div>@endif
                        </td>
                        <td class="text-end fw-semibold text-nowrap">{{ number_format($p->amount) }} XAF</td>
                        @if($showCommission)
                            <td class="text-end text-nowrap text-muted small">
                                @if($p->isApproved()){{ number_format($p->commission_amount) }} <span class="d-block" style="font-size:.7rem;">{{ rtrim(rtrim(number_format($p->commission_rate, 2), '0'), '.') }}%</span>@else — @endif
                            </td>
                            <td class="text-end text-nowrap">@if($p->isApproved()){{ number_format($p->netAmount()) }} XAF @else — @endif</td>
                        @endif
                        <td class="text-nowrap">{{ $p->paid_on->translatedFormat('d M Y') }}</td>
                        <td>
                            {{ $p->methodLabel() }}
                            @if($p->transaction_ref)<div class="small text-muted font-monospace" style="font-size:.7rem;">{{ $p->transaction_ref }}</div>@endif
                        </td>
                        <td>
                            <span class="badge-soft badge-soft--{{ $tone[$p->status] }} text-nowrap">{{ __(ucfirst($p->status)) }}</span>
                            @if($p->isRejected() && $p->rejection_reason)
                                <div class="small text-muted">{{ $p->rejection_reason }}</div>
                            @endif
                        </td>
                        <td class="text-end">
                            @if($p->isApproved())
                                <a href="{{ route('payments.receipt', $p) }}" class="btn btn-sm btn-outline-dark text-nowrap">{{ __('Receipt') }}</a>
                            @elseif($p->isPending() && $user->isAdmin())
                                <div class="d-flex gap-1 justify-content-end">
                                    <form method="POST" action="{{ route('payments.approve', $p) }}">
                                        @csrf
                                        <button class="btn btn-sm btn-success text-nowrap">{{ __('Approve') }}</button>
                                    </form>
                                    <button type="button" class="btn btn-sm btn-outline-danger text-nowrap" data-bs-toggle="collapse" data-bs-target="#reject-{{ $p->id }}">{{ __('Reject') }}</button>
                                </div>
                                <div class="collapse mt-2" id="reject-{{ $p->id }}">
                                    <form method="POST" action="{{ route('payments.reject', $p) }}" class="d-flex gap-1">
                                        @csrf
                                        <input type="text" name="rejection_reason" class="form-control form-control-sm" placeholder="{{ __('Reason') }}" required>
                                        <button class="btn btn-sm btn-danger">{{ __('Confirm') }}</button>
                                    </form>
                                </div>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="{{ $cols }}" class="text-muted text-center py-5">{{ __('No payments recorded yet') }}</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="mt-3">{{ $payments->links() }}</div>
@endsection
