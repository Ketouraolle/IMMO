@extends('layouts.app')
@section('title', 'Admin Dashboard')
@section('content')
    <h3 class="mb-4">Admin Dashboard</h3>

    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="card stat-card"><div class="card-body">
                <div class="text-muted small">Properties</div>
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
        <div class="col-6 col-md-3">
            <div class="card stat-card {{ $pendingPayments ? 'border border-warning' : '' }}"><div class="card-body">
                <div class="text-muted small">Payments to review</div>
                <div class="value">{{ $pendingPayments }}</div>
                @if($pendingPayments)
                    <a href="{{ route('payments.index', ['status'=>'pending']) }}" class="small">Review now &rarr;</a>
                @endif
            </div></div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-12">
            <div class="d-flex gap-2 flex-wrap">
                <a href="{{ route('properties.create') }}" class="btn btn-dark btn-sm">+ Add property</a>
                <a href="{{ route('users.create') }}" class="btn btn-outline-dark btn-sm">+ Create account</a>
                <a href="{{ route('properties.index') }}" class="btn btn-outline-dark btn-sm">View properties</a>
                <a href="{{ route('issues.index') }}" class="btn btn-outline-dark btn-sm">View issues</a>
            </div>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-white fw-semibold">Recent payments</div>
                <div class="table-responsive">
                    <table class="table mb-0">
                        <thead><tr><th>Tenant</th><th>Property</th><th>Amount</th><th>Date</th></tr></thead>
                        <tbody>
                        @forelse($recentPayments as $p)
                            <tr>
                                <td>{{ $p->lease->tenant->name }}</td>
                                <td>{{ $p->lease->property->name }}</td>
                                <td>{{ number_format($p->amount) }} XAF</td>
                                <td>{{ $p->paid_on->format('d M Y') }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="text-muted text-center py-3">No payments yet</td></tr>
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
                            <tr><td colspan="3" class="text-muted text-center py-3">No issues yet</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
