@extends('layouts.app')
@section('title', auth()->user()->isTenant() ? __('Payment history') : __('Payments'))
@section('content')
    @php
        $user = auth()->user();
        $isTenant = $user->isTenant();
        $showCommission = ! $isTenant;
        $tone = ['pending' => 'warning', 'approved' => 'success', 'rejected' => 'danger'];
        $cols = $isTenant ? 7 : 9;
        $statusUrl = fn (?string $status) => route('payments.index', array_filter(array_merge(request()->except(['status', 'page']), ['status' => $status])));
        $net = fn (float $amount, float $commission) => number_format($amount - $commission);
    @endphp

    <div class="page-head">
        <div>
            <h3>{{ $isTenant ? __('Payment history') : __('Payments') }}</h3>
            <p class="page-head__sub">
                {{ $isTenant ? __('Every rent payment on your lease, with receipts.') : ($user->isOwner() ? __('Net amounts are after the agency commission.') : __('All rent payments across the portfolio.')) }}
            </p>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <a href="{{ route('payments.export', request()->except('page')) }}" class="btn btn-outline-dark btn-sm"><i class="bi bi-download"></i> {{ __('Export CSV') }}</a>
            @if($isTenant && $lease)
                <a href="{{ route('payments.submit-form') }}" class="btn btn-dark btn-sm"><i class="bi bi-phone"></i> {{ __('Pay rent') }}</a>
            @endif
        </div>
    </div>

    {{-- Summary --}}
    @if($isTenant)
        @if($lease)
            @include('payments._rent-status', ['lease' => $lease])
        @endif
        <div class="row g-3 mb-4">
            <div class="col-sm-4">
                @include('partials.stat-card', ['icon' => 'bi-wallet2', 'label' => __('Paid this year'), 'value' => number_format($stats['yearAmount']).' XAF'])
            </div>
            <div class="col-sm-4">
                @include('partials.stat-card', [
                    'icon' => 'bi-receipt', 'label' => __('Last payment'),
                    'value' => $stats['lastPayment'] ? number_format($stats['lastPayment']->amount).' XAF' : '—',
                    'hint' => $stats['lastPayment'] ? $stats['lastPayment']->paid_on->translatedFormat('j F Y') : __('No payments yet'),
                ])
            </div>
            <div class="col-sm-4">
                @include('partials.stat-card', [
                    'icon' => 'bi-hourglass-split', 'label' => __('Awaiting review'), 'value' => $stats['pendingCount'],
                    'hint' => $stats['pendingCount'] ? number_format($stats['pendingAmount']).' XAF' : null,
                    'tone' => $stats['pendingCount'] ? 'warning' : null,
                ])
            </div>
        </div>
    @else
        <div class="row g-3 mb-4">
            <div class="col-sm-6 col-xl-3">
                @include('partials.stat-card', [
                    'icon' => 'bi-wallet2', 'label' => __('Collected this month'), 'value' => number_format($stats['monthAmount']).' XAF',
                ])
            </div>
            <div class="col-sm-6 col-xl-3">
                @if($user->isOwner())
                    @include('partials.stat-card', [
                        'icon' => 'bi-piggy-bank', 'label' => __('Net to you this month'), 'value' => $net($stats['monthAmount'], $stats['monthCommission']).' XAF',
                    ])
                @else
                    @include('partials.stat-card', [
                        'icon' => 'bi-percent', 'label' => __('Commission this month'), 'value' => number_format($stats['monthCommission']).' XAF',
                    ])
                @endif
            </div>
            <div class="col-sm-6 col-xl-3">
                @include('partials.stat-card', [
                    'icon' => 'bi-graph-up-arrow', 'label' => __('Collected this year'), 'value' => number_format($stats['yearAmount']).' XAF',
                    'hint' => $user->isOwner()
                        ? __('Net: :amount XAF', ['amount' => $net($stats['yearAmount'], $stats['yearCommission'])])
                        : __('Commission: :amount XAF', ['amount' => number_format($stats['yearCommission'])]),
                ])
            </div>
            <div class="col-sm-6 col-xl-3">
                @include('partials.stat-card', [
                    'icon' => 'bi-hourglass-split', 'label' => __('Awaiting review'), 'value' => $stats['pendingCount'],
                    'hint' => $stats['pendingCount'] ? number_format($stats['pendingAmount']).' XAF' : null,
                    'href' => $stats['pendingCount'] && $user->isAdmin() && $filters['status'] !== 'pending' ? $statusUrl('pending') : null,
                    'linkLabel' => __('Review'),
                    'tone' => $stats['pendingCount'] ? 'warning' : null,
                ])
            </div>
        </div>
    @endif

    {{-- Filters --}}
    <div class="card mb-3">
        <div class="card-body py-3">
            <form method="GET" action="{{ route('payments.index') }}" class="row g-2 align-items-end">
                @if($filters['status'])<input type="hidden" name="status" value="{{ $filters['status'] }}">@endif
                @unless($isTenant)
                    <div class="col-12 col-md-6 col-xl-3">
                        <label class="form-label small mb-1" for="filter-q">{{ __('Search') }}</label>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text"><i class="bi bi-search"></i></span>
                            <input id="filter-q" type="search" name="q" value="{{ $filters['q'] }}" class="form-control" placeholder="{{ __('Tenant, property, receipt or reference') }}">
                        </div>
                    </div>
                    <div class="col-6 col-md-3 col-xl-2">
                        <label class="form-label small mb-1" for="filter-property">{{ __('Property') }}</label>
                        <select id="filter-property" name="property" class="form-select form-select-sm">
                            <option value="">{{ __('All properties') }}</option>
                            @foreach($properties as $property)
                                <option value="{{ $property->id }}" @selected($filters['property'] === $property->id)>{{ $property->name }}</option>
                            @endforeach
                        </select>
                    </div>
                @endunless
                <div class="col-6 col-md-3 col-xl-2">
                    <label class="form-label small mb-1" for="filter-method">{{ __('Method') }}</label>
                    <select id="filter-method" name="method" class="form-select form-select-sm">
                        <option value="">{{ __('All methods') }}</option>
                        @foreach(\App\Models\Payment::METHODS as $value => $label)
                            <option value="{{ $value }}" @selected($filters['method'] === $value)>{{ __($label) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6 col-md-3 col-xl">
                    <label class="form-label small mb-1" for="filter-from">{{ __('From') }}</label>
                    <input id="filter-from" type="date" name="from" value="{{ $filters['from'] }}" class="form-control form-control-sm">
                </div>
                <div class="col-6 col-md-3 col-xl">
                    <label class="form-label small mb-1" for="filter-to">{{ __('To') }}</label>
                    <input id="filter-to" type="date" name="to" value="{{ $filters['to'] }}" class="form-control form-control-sm">
                </div>
                <div class="col-12 col-md-auto d-flex gap-1">
                    <button class="btn btn-dark btn-sm px-3">{{ __('Apply') }}</button>
                    @if($hasFilters)
                        <a href="{{ route('payments.index', array_filter(['status' => $filters['status']])) }}" class="btn btn-link btn-sm text-decoration-none">{{ __('Clear') }}</a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <div class="pill-nav">
            <a href="{{ $statusUrl(null) }}" class="{{ $filters['status'] ? '' : 'active' }}">{{ __('All') }}</a>
            @foreach(['pending' => __('Pending'), 'approved' => __('Approved'), 'rejected' => __('Rejected')] as $value => $label)
                <a href="{{ $statusUrl($value) }}" class="{{ $filters['status'] === $value ? 'active' : '' }}">{{ $label }}</a>
            @endforeach
        </div>
        <span class="small text-muted">{{ trans_choice(':count payment|:count payments', $payments->total()) }}</span>
    </div>

    {{-- History --}}
    <div class="card">
        <div class="table-responsive">
            <table class="table mb-0 align-middle">
                <thead>
                <tr>
                    <th>{{ $isTenant ? __('Property') : __('Tenant') }}</th>
                    <th>{{ __('Period') }}</th>
                    <th class="text-end">{{ __('Amount') }}</th>
                    @if($showCommission)<th class="text-end">{{ __('Commission') }}</th><th class="text-end">{{ __('Net to owner') }}</th>@endif
                    <th>{{ __('Date') }}</th><th>{{ __('Method') }}</th><th>{{ __('Status') }}</th><th></th>
                </tr>
                </thead>
                <tbody>
                @forelse($payments as $p)
                    <tr>
                        <td>
                            @unless($isTenant)<div class="fw-semibold">{{ $p->lease->tenant->name }}</div>@endunless
                            <div class="{{ $isTenant ? 'fw-semibold' : 'small text-muted' }}">{{ $p->lease->property->name }}</div>
                            @if($p->receipt_number)<div class="small text-muted font-monospace" style="font-size:.7rem;">{{ $p->receipt_number }}</div>@endif
                        </td>
                        <td class="small">{{ $p->period_covered ?? '—' }}</td>
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
                    <tr>
                        <td colspan="{{ $cols }}" class="text-muted text-center py-5">
                            <i class="bi bi-receipt d-block fs-3 mb-2 opacity-50"></i>
                            @if($hasFilters || $filters['status'])
                                {{ __('No payments match these filters') }} ·
                                <a href="{{ route('payments.index') }}">{{ __('Clear filters') }}</a>
                            @else
                                {{ __('No payments recorded yet') }}
                            @endif
                        </td>
                    </tr>
                @endforelse
                </tbody>
                @if($totals->count)
                    <tfoot>
                    <tr class="table-totals">
                        <td colspan="2" class="fw-semibold">{{ trans_choice('Total approved (:count payment)|Total approved (:count payments)', $totals->count) }}</td>
                        <td class="text-end fw-bold text-nowrap">{{ number_format($totals->amount) }} XAF</td>
                        @if($showCommission)
                            <td class="text-end text-nowrap text-muted small">{{ number_format($totals->commission) }}</td>
                            <td class="text-end fw-bold text-nowrap">{{ $net((float) $totals->amount, (float) $totals->commission) }} XAF</td>
                        @endif
                        <td colspan="4"></td>
                    </tr>
                    </tfoot>
                @endif
            </table>
        </div>
    </div>
    <div class="mt-3">{{ $payments->links() }}</div>
@endsection
