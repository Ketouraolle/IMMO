@extends('layouts.app')
@section('title', 'My Dashboard')
@section('content')
    <h3 class="mb-4">Welcome, {{ auth()->user()->name }}</h3>

    @if($lease)
        <div class="card mb-4">
            <div class="card-header bg-white fw-semibold d-flex justify-content-between align-items-center">
                Your lease
                <div class="d-flex gap-2">
                    @if($lease->document_path)
                        <a href="{{ asset('storage/' . $lease->document_path) }}" target="_blank" class="btn btn-sm btn-outline-dark">View contract (PDF)</a>
                    @endif
                    <a href="{{ route('payments.submit-form') }}" class="btn btn-sm btn-dark">Submit a payment</a>
                </div>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-6 col-md-3">
                        <div class="text-muted small">Property</div>
                        <div class="fw-semibold">{{ $lease->property->name }}</div>
                        <div class="text-muted small">{{ $lease->property->address }}</div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="text-muted small">Rent</div>
                        <div class="fw-semibold">{{ number_format($lease->rent_amount) }} XAF / {{ $lease->billing_cycle }}</div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="text-muted small">Lease start</div>
                        <div class="fw-semibold">{{ $lease->start_date->format('d M Y') }}</div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="text-muted small">Status</div>
                        <span class="badge bg-success">{{ ucfirst($lease->status) }}</span>
                    </div>
                </div>
                @if(!$lease->document_path)
                    <div class="text-muted small mt-3">Your signed contract hasn't been uploaded by the office yet.</div>
                @endif
            </div>
        </div>
    @else
        <div class="alert alert-warning">You don't have an active lease on file. Contact the office if this looks wrong.</div>
    @endif

    <div class="row g-3">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-white fw-semibold d-flex justify-content-between align-items-center">
                    Payment history
                    <a href="{{ route('payments.index') }}" class="small">View all</a>
                </div>
                <div class="table-responsive">
                    <table class="table mb-0">
                        <thead><tr><th>Amount</th><th>Date</th><th>Status</th><th></th></tr></thead>
                        <tbody>
                        @forelse($payments->take(6) as $p)
                            <tr>
                                <td>{{ number_format($p->amount) }} XAF</td>
                                <td>{{ $p->paid_on->format('d M Y') }}</td>
                                <td>
                                    @php $badge = ['pending'=>'bg-warning text-dark','approved'=>'bg-success','rejected'=>'bg-danger']; @endphp
                                    <span class="badge {{ $badge[$p->status] }}">{{ ucfirst($p->status) }}</span>
                                </td>
                                <td>
                                    @if($p->status=='approved')
                                        <a href="{{ route('payments.receipt', $p) }}" class="small">Receipt</a>
                                    @endif
                                </td>
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
                <div class="card-header bg-white fw-semibold d-flex justify-content-between align-items-center">
                    Your reported issues
                    <a href="{{ route('issues.create') }}" class="small">+ Report new</a>
                </div>
                <div class="table-responsive">
                    <table class="table mb-0">
                        <thead><tr><th>Title</th><th>Priority</th><th>Status</th></tr></thead>
                        <tbody>
                        @forelse($issues->take(6) as $i)
                            <tr>
                                <td><a href="{{ route('issues.show', $i) }}">{{ $i->title }}</a></td>
                                <td>{{ ucfirst($i->priority) }}</td>
                                <td><span class="badge bg-secondary">{{ ucfirst(str_replace('_',' ',$i->status)) }}</span></td>
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
