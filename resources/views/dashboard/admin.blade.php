@extends('layouts.app')
@section('title', __('Dashboard'))
@section('content')
    @php
        $firstName = Str::before(auth()->user()->name, ' ');
        $greeting = now()->hour < 12
            ? __('Good morning, :name', ['name' => $firstName])
            : (now()->hour < 18 ? __('Good afternoon, :name', ['name' => $firstName]) : __('Good evening, :name', ['name' => $firstName]));
        $visitTone = ['new' => 'info', 'confirmed' => 'success'];
        $occupancy = $propertyCount ? round($occupiedCount / $propertyCount * 100) : 0;
    @endphp

    <div class="page-head">
        <div>
            <h3>{{ $greeting }}</h3>
            <p class="page-head__sub">{{ __("Here's what's happening across the portfolio.") }}</p>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <a href="{{ route('users.create') }}" class="btn btn-outline-dark btn-sm"><i class="bi bi-person-plus"></i> {{ __('New account') }}</a>
            <a href="{{ route('properties.create') }}" class="btn btn-dark btn-sm"><i class="bi bi-plus-lg"></i> {{ __('Add property') }}</a>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-3">
            @include('partials.stat-card', [
                'icon' => 'bi-buildings', 'label' => __('Properties'), 'value' => $propertyCount,
                'hint' => __(':occupied occupied · :vacant vacant · :rate% occupancy', ['occupied' => $occupiedCount, 'vacant' => $vacantCount, 'rate' => $occupancy]),
            ])
        </div>
        <div class="col-sm-6 col-xl-3">
            @include('partials.stat-card', [
                'icon' => 'bi-wallet2', 'label' => __('Collected this month'), 'value' => number_format($thisMonthCollected).' XAF',
                'hint' => __('Commission earned: :amount XAF', ['amount' => number_format($commissionThisMonth)]),
            ])
        </div>
        <div class="col-sm-6 col-xl-3">
            @include('partials.stat-card', [
                'icon' => 'bi-calendar2-week', 'label' => __('New visit requests'), 'value' => $newVisitRequests,
                'hint' => __('Visit fees this month: :amount XAF', ['amount' => number_format($visitFeesThisMonth)]),
                'href' => $newVisitRequests ? route('visit-requests.index', ['status' => 'new']) : null, 'linkLabel' => __('Review'),
                'tone' => $newVisitRequests ? 'warning' : null,
            ])
        </div>
        <div class="col-sm-6 col-xl-3">
            @include('partials.stat-card', [
                'icon' => 'bi-hourglass-split', 'label' => __('Payments to review'), 'value' => $pendingPayments,
                'hint' => trans_choice(':count open issue|:count open issues', $openIssues),
                'href' => $pendingPayments ? route('payments.index', ['status' => 'pending']) : null, 'linkLabel' => __('Review'),
                'tone' => $pendingPayments ? 'warning' : null,
            ])
        </div>
    </div>

    <div class="row g-3">
        <div class="col-xl-6">
            <div class="card h-100">
                <div class="card-header fw-semibold d-flex justify-content-between align-items-center">
                    {{ __('Upcoming visits') }}
                    <a href="{{ route('visit-requests.index') }}" class="small fw-normal text-decoration-none">{{ __('View all') }}</a>
                </div>
                <ul class="list-group list-group-flush">
                    @forelse($upcomingVisits as $vr)
                        <li class="list-group-item d-flex align-items-center gap-3 py-3">
                            <div class="text-center flex-shrink-0" style="width:2.8rem;">
                                <div class="text-muted text-uppercase" style="font-size:.66rem;">{{ $vr->visit_date->translatedFormat('M') }}</div>
                                <div class="fw-bold fs-5 lh-1">{{ $vr->visit_date->format('d') }}</div>
                            </div>
                            <div class="flex-grow-1 min-w-0">
                                <a href="{{ route('visit-requests.show', $vr) }}" class="fw-semibold text-decoration-none text-reset">{{ $vr->name }}</a>
                                <div class="small text-muted text-truncate">{{ $vr->visitTimeLabel() }} · {{ $vr->property->name }}</div>
                            </div>
                            <div class="text-end d-none d-sm-block">
                                @include('visit-requests._payment-badge', ['vr' => $vr])
                                <div class="mt-1"><span class="badge-soft badge-soft--{{ $visitTone[$vr->status] }}">{{ $vr->statusLabel() }}</span></div>
                            </div>
                        </li>
                    @empty
                        <li class="list-group-item text-muted text-center py-5">{{ __('No upcoming visits') }}</li>
                    @endforelse
                </ul>
            </div>
        </div>
        <div class="col-xl-6">
            <div class="card h-100">
                <div class="card-header fw-semibold d-flex justify-content-between align-items-center">
                    {{ __('Recent payments') }}
                    <a href="{{ route('payments.index') }}" class="small fw-normal text-decoration-none">{{ __('View all') }}</a>
                </div>
                <div class="table-responsive">
                    <table class="table mb-0">
                        <thead><tr><th>{{ __('Tenant') }}</th><th class="text-end">{{ __('Amount') }}</th><th>{{ __('Date') }}</th></tr></thead>
                        <tbody>
                        @forelse($recentPayments as $p)
                            <tr>
                                <td>{{ $p->lease->tenant->name }}<div class="small text-muted">{{ $p->lease->property->name }}</div></td>
                                <td class="text-end text-nowrap fw-semibold">{{ number_format($p->amount) }} XAF</td>
                                <td class="text-nowrap">{{ $p->paid_on->translatedFormat('d M Y') }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="text-muted text-center py-5">{{ __('No payments yet') }}</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-12">
            <div class="card">
                <div class="card-header fw-semibold d-flex justify-content-between align-items-center">
                    {{ __('Recent issues') }}
                    <a href="{{ route('issues.index') }}" class="small fw-normal text-decoration-none">{{ __('View all') }}</a>
                </div>
                <div class="table-responsive">
                    <table class="table mb-0">
                        <thead><tr><th>{{ __('Title') }}</th><th>{{ __('Property') }}</th><th>{{ __('Priority') }}</th><th>{{ __('Status') }}</th></tr></thead>
                        <tbody>
                        @forelse($recentIssues as $i)
                            <tr>
                                <td><a href="{{ route('issues.show', $i) }}">{{ $i->title }}</a></td>
                                <td>{{ $i->property->name }}</td>
                                <td>{{ __(ucfirst($i->priority)) }}</td>
                                <td><span class="badge-soft badge-soft--neutral">{{ __(ucfirst(str_replace('_', ' ', $i->status))) }}</span></td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="text-muted text-center py-4">{{ __('No issues yet') }}</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
