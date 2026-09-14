@extends('layouts.app')
@section('title', 'Dashboard')
@section('content')
    @php
        $greeting = now()->hour < 12 ? 'Good morning' : (now()->hour < 18 ? 'Good afternoon' : 'Good evening');
        $visitTone = ['new' => 'info', 'confirmed' => 'success'];
    @endphp

    <div class="page-head">
        <div>
            <h3>{{ $greeting }}, {{ Str::before(auth()->user()->name, ' ') }}</h3>
            <p class="page-head__sub">Here's what's happening across the portfolio.</p>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <a href="{{ route('users.create') }}" class="btn btn-outline-dark btn-sm"><i class="bi bi-person-plus"></i> New account</a>
            <a href="{{ route('properties.create') }}" class="btn btn-dark btn-sm"><i class="bi bi-plus-lg"></i> Add property</a>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-3">
            @include('partials.stat-card', [
                'icon' => 'bi-buildings', 'label' => 'Properties', 'value' => $propertyCount,
                'hint' => "$occupiedCount occupied · $vacantCount vacant · ".($propertyCount ? round($occupiedCount / $propertyCount * 100) : 0).'% occupancy',
            ])
        </div>
        <div class="col-sm-6 col-xl-3">
            @include('partials.stat-card', [
                'icon' => 'bi-wallet2', 'label' => 'Collected this month', 'value' => number_format($thisMonthCollected).' XAF',
                'hint' => 'Commission earned: '.number_format($commissionThisMonth).' XAF',
            ])
        </div>
        <div class="col-sm-6 col-xl-3">
            @include('partials.stat-card', [
                'icon' => 'bi-calendar2-week', 'label' => 'New visit requests', 'value' => $newVisitRequests,
                'hint' => 'Visit fees this month: '.number_format($visitFeesThisMonth).' XAF',
                'href' => $newVisitRequests ? route('visit-requests.index', ['status' => 'new']) : null, 'linkLabel' => 'Review',
                'tone' => $newVisitRequests ? 'warning' : null,
            ])
        </div>
        <div class="col-sm-6 col-xl-3">
            @include('partials.stat-card', [
                'icon' => 'bi-hourglass-split', 'label' => 'Payments to review', 'value' => $pendingPayments,
                'hint' => "$openIssues open ".Str::plural('issue', $openIssues),
                'href' => $pendingPayments ? route('payments.index', ['status' => 'pending']) : null, 'linkLabel' => 'Review',
                'tone' => $pendingPayments ? 'warning' : null,
            ])
        </div>
    </div>

    <div class="row g-3">
        <div class="col-xl-6">
            <div class="card h-100">
                <div class="card-header bg-white fw-semibold d-flex justify-content-between align-items-center">
                    Upcoming visits
                    <a href="{{ route('visit-requests.index') }}" class="small fw-normal text-decoration-none">View all</a>
                </div>
                <ul class="list-group list-group-flush">
                    @forelse($upcomingVisits as $vr)
                        <li class="list-group-item d-flex align-items-center gap-3 py-3">
                            <div class="text-center flex-shrink-0" style="width:2.8rem;">
                                <div class="text-muted text-uppercase" style="font-size:.66rem;">{{ $vr->visit_date->format('M') }}</div>
                                <div class="fw-bold fs-5 lh-1">{{ $vr->visit_date->format('d') }}</div>
                            </div>
                            <div class="flex-grow-1 min-w-0">
                                <a href="{{ route('visit-requests.show', $vr) }}" class="fw-semibold text-decoration-none text-reset">{{ $vr->name }}</a>
                                <div class="small text-muted text-truncate">{{ $vr->visitTimeLabel() }} · {{ $vr->property->name }}</div>
                            </div>
                            <div class="text-end d-none d-sm-block">
                                @include('visit-requests._payment-badge', ['vr' => $vr])
                                <div class="mt-1"><span class="badge-soft badge-soft--{{ $visitTone[$vr->status] }}">{{ ucfirst($vr->status) }}</span></div>
                            </div>
                        </li>
                    @empty
                        <li class="list-group-item text-muted text-center py-5">No upcoming visits</li>
                    @endforelse
                </ul>
            </div>
        </div>
        <div class="col-xl-6">
            <div class="card h-100">
                <div class="card-header bg-white fw-semibold d-flex justify-content-between align-items-center">
                    Recent payments
                    <a href="{{ route('payments.index') }}" class="small fw-normal text-decoration-none">View all</a>
                </div>
                <div class="table-responsive">
                    <table class="table mb-0">
                        <thead><tr><th>Tenant</th><th class="text-end">Amount</th><th>Date</th></tr></thead>
                        <tbody>
                        @forelse($recentPayments as $p)
                            <tr>
                                <td>{{ $p->lease->tenant->name }}<div class="small text-muted">{{ $p->lease->property->name }}</div></td>
                                <td class="text-end text-nowrap fw-semibold">{{ number_format($p->amount) }} XAF</td>
                                <td class="text-nowrap">{{ $p->paid_on->format('d M Y') }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="text-muted text-center py-5">No payments yet</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-white fw-semibold d-flex justify-content-between align-items-center">
                    Recent issues
                    <a href="{{ route('issues.index') }}" class="small fw-normal text-decoration-none">View all</a>
                </div>
                <div class="table-responsive">
                    <table class="table mb-0">
                        <thead><tr><th>Title</th><th>Property</th><th>Priority</th><th>Status</th></tr></thead>
                        <tbody>
                        @forelse($recentIssues as $i)
                            <tr>
                                <td><a href="{{ route('issues.show', $i) }}">{{ $i->title }}</a></td>
                                <td>{{ $i->property->name }}</td>
                                <td>{{ ucfirst($i->priority) }}</td>
                                <td><span class="badge-soft badge-soft--neutral">{{ ucfirst(str_replace('_', ' ', $i->status)) }}</span></td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="text-muted text-center py-4">No issues yet</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
