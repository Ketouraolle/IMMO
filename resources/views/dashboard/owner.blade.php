@extends('layouts.app')
@section('title', 'Owner Dashboard')
@section('content')
    <h3 class="mb-4">Welcome back, {{ auth()->user()->name }}</h3>

    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="card stat-card"><div class="card-body">
                <div class="text-muted small">Your properties</div>
                <div class="value">{{ $propertyCount }}</div>
                <div class="small text-muted">{{ $occupiedCount }} occupied · {{ $vacantCount }} vacant</div>
            </div></div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card stat-card"><div class="card-body">
                <div class="text-muted small">Collected this month</div>
                <div class="value">{{ number_format($thisMonthCollected) }} XAF</div>
            </div></div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card stat-card"><div class="card-body">
                <div class="text-muted small">Open issues</div>
                <div class="value">{{ $openIssues }}</div>
            </div></div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card stat-card"><div class="card-body">
                <div class="text-muted small">Occupancy rate</div>
                <div class="value">{{ $propertyCount ? round($occupiedCount / $propertyCount * 100) : 0 }}%</div>
            </div></div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header bg-white fw-semibold">Your properties</div>
        <div class="table-responsive">
            <table class="table mb-0 align-middle">
                <thead><tr><th>Property</th><th>Status</th><th>Tenant</th><th>Monthly rent</th><th>Total collected</th><th></th></tr></thead>
                <tbody>
                @forelse($properties as $p)
                    <tr>
                        <td>{{ $p->name }}</td>
                        <td>
                            @php $badge = ['vacant'=>'bg-warning text-dark','occupied'=>'bg-success','maintenance'=>'bg-secondary']; @endphp
                            <span class="badge {{ $badge[$p->status] }}">{{ ucfirst($p->status) }}</span>
                        </td>
                        <td>{{ $p->activeLease?->tenant?->name ?? '—' }}</td>
                        <td>{{ number_format($p->monthly_rent) }} XAF</td>
                        <td>{{ number_format($p->total_collected ?? 0) }} XAF</td>
                        <td><a href="{{ route('properties.show', $p) }}" class="btn btn-sm btn-outline-dark">View</a></td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-muted text-center py-4">No properties assigned to you yet</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-white fw-semibold">Recent payments</div>
                <div class="table-responsive">
                    <table class="table mb-0">
                        <thead><tr><th>Property</th><th>Amount</th><th>Date</th></tr></thead>
                        <tbody>
                        @forelse($recentPayments as $p)
                            <tr>
                                <td>{{ $p->lease->property->name }}</td>
                                <td>{{ number_format($p->amount) }} XAF</td>
                                <td>{{ $p->paid_on->format('d M Y') }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="text-muted text-center py-3">No payments yet</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-white fw-semibold">Recent issues</div>
                <div class="table-responsive">
                    <table class="table mb-0">
                        <thead><tr><th>Title</th><th>Property</th><th>Status</th></tr></thead>
                        <tbody>
                        @forelse($recentIssues as $i)
                            <tr>
                                <td><a href="{{ route('issues.show', $i) }}">{{ $i->title }}</a></td>
                                <td>{{ $i->property->name }}</td>
                                <td><span class="badge bg-secondary">{{ ucfirst(str_replace('_',' ', $i->status)) }}</span></td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="text-muted text-center py-3">No issues reported</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
