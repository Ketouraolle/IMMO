@extends('layouts.app')
@section('title', 'Dashboard')
@section('content')
    @php $statusTone = ['vacant' => 'warning', 'occupied' => 'success', 'maintenance' => 'neutral']; @endphp

    <div class="page-head">
        <div>
            <h3>Welcome back, {{ auth()->user()->name }}</h3>
            <p class="page-head__sub">Your properties and earnings at a glance.</p>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-3">
            @include('partials.stat-card', [
                'icon' => 'bi-buildings', 'label' => 'Your properties', 'value' => $propertyCount,
                'hint' => "$occupiedCount occupied · $vacantCount vacant",
            ])
        </div>
        <div class="col-sm-6 col-xl-3">
            @include('partials.stat-card', [
                'icon' => 'bi-wallet2', 'label' => 'Collected this month', 'value' => number_format($thisMonthCollected).' XAF',
                'hint' => 'Gross rent received',
            ])
        </div>
        <div class="col-sm-6 col-xl-3">
            @include('partials.stat-card', [
                'icon' => 'bi-piggy-bank', 'label' => 'Net to you this month', 'value' => number_format($thisMonthCollected - $commissionThisMonth).' XAF',
                'hint' => 'After '.number_format($commissionThisMonth).' XAF agency commission',
            ])
        </div>
        <div class="col-sm-6 col-xl-3">
            @include('partials.stat-card', [
                'icon' => 'bi-tools', 'label' => 'Open issues', 'value' => $openIssues,
                'hint' => ($propertyCount ? round($occupiedCount / $propertyCount * 100) : 0).'% occupancy',
                'href' => $openIssues ? route('issues.index') : null,
                'tone' => $openIssues ? 'warning' : null,
            ])
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header bg-white fw-semibold">Your properties</div>
        <div class="table-responsive">
            <table class="table mb-0 align-middle">
                <thead><tr><th>Property</th><th>Status</th><th>Tenant</th><th class="text-end">Rent</th><th class="text-end">Collected</th><th class="text-end">Commission</th><th class="text-end">Net</th><th></th></tr></thead>
                <tbody>
                @forelse($properties as $p)
                    <tr>
                        <td><a href="{{ route('properties.show', $p) }}">{{ $p->name }}</a></td>
                        <td><span class="badge-soft badge-soft--{{ $statusTone[$p->status] }}">{{ ucfirst($p->status) }}</span></td>
                        <td>{{ $p->activeLease?->tenant?->name ?? '—' }}</td>
                        <td class="text-end text-nowrap">{{ number_format($p->monthly_rent) }}</td>
                        <td class="text-end text-nowrap">{{ number_format($p->total_collected ?? 0) }}</td>
                        <td class="text-end text-nowrap text-muted">{{ number_format($p->total_commission ?? 0) }} <span class="small">({{ rtrim(rtrim(number_format($p->commission_rate, 2), '0'), '.') }}%)</span></td>
                        <td class="text-end text-nowrap fw-semibold">{{ number_format(($p->total_collected ?? 0) - ($p->total_commission ?? 0)) }} XAF</td>
                        <td class="text-end"><a href="{{ route('properties.show', $p) }}" class="btn btn-sm btn-outline-dark">View</a></td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="text-muted text-center py-5">No properties assigned to you yet</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-header bg-white fw-semibold d-flex justify-content-between align-items-center">
                    Recent payments
                    <a href="{{ route('payments.index') }}" class="small fw-normal text-decoration-none">View all</a>
                </div>
                <div class="table-responsive">
                    <table class="table mb-0">
                        <thead><tr><th>Property</th><th class="text-end">Net</th><th>Date</th></tr></thead>
                        <tbody>
                        @forelse($recentPayments as $p)
                            <tr>
                                <td>{{ $p->lease->property->name }}</td>
                                <td class="text-end text-nowrap fw-semibold">{{ number_format($p->netAmount()) }} XAF</td>
                                <td class="text-nowrap">{{ $p->paid_on->format('d M Y') }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="text-muted text-center py-4">No payments yet</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-header bg-white fw-semibold">Recent issues</div>
                <div class="table-responsive">
                    <table class="table mb-0">
                        <thead><tr><th>Title</th><th>Property</th><th>Status</th></tr></thead>
                        <tbody>
                        @forelse($recentIssues as $i)
                            <tr>
                                <td><a href="{{ route('issues.show', $i) }}">{{ $i->title }}</a></td>
                                <td>{{ $i->property->name }}</td>
                                <td><span class="badge-soft badge-soft--neutral">{{ ucfirst(str_replace('_', ' ', $i->status)) }}</span></td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="text-muted text-center py-4">No issues reported</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
