@extends('layouts.app')
@section('title', 'Dashboard')
@section('content')
    @php $tone = ['pending' => 'warning', 'approved' => 'success', 'rejected' => 'danger']; @endphp

    <div class="page-head">
        <div>
            <h3>Welcome, {{ Str::before(auth()->user()->name, ' ') }}</h3>
            <p class="page-head__sub">Your home at a glance.</p>
        </div>
        @if($lease)
            <a href="{{ route('payments.submit-form') }}" class="btn btn-dark btn-sm"><i class="bi bi-phone"></i> Pay rent</a>
        @endif
    </div>

    @if($contract?->isSent())
        <div class="card mb-4" style="background: linear-gradient(135deg, var(--ink) 0%, #1f3a63 100%); color: #fff;">
            <div class="card-body d-flex flex-wrap gap-3 align-items-center p-4">
                <span class="stat-icon" style="background: rgba(255,255,255,.12); color: #fff;"><i class="bi bi-pen"></i></span>
                <div class="flex-grow-1">
                    <div class="fw-bold">Your lease contract is ready to sign</div>
                    <div class="small" style="opacity:.75;">Sent {{ $contract->sent_at->diffForHumans() }}. Read it and sign online in a minute.</div>
                </div>
                <a href="{{ route('contracts.show', $contract) }}" class="btn btn-light btn-sm fw-semibold">Review & sign</a>
            </div>
        </div>
    @endif

    @if($lease)
        <div class="card mb-4">
            <div class="card-header bg-white fw-semibold d-flex justify-content-between align-items-center flex-wrap gap-2">
                Your lease
                <div class="d-flex gap-2">
                    @if($contract?->isSigned())
                        <a href="{{ route('contracts.show', $contract) }}" class="btn btn-sm btn-outline-dark"><i class="bi bi-file-earmark-check"></i> Signed contract</a>
                    @elseif($lease->document_path)
                        <a href="{{ asset('storage/' . $lease->document_path) }}" target="_blank" class="btn btn-sm btn-outline-dark">View contract (PDF)</a>
                    @endif
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
                        <span class="badge-soft badge-soft--success">{{ ucfirst($lease->status) }}</span>
                    </div>
                </div>
            </div>
        </div>
    @else
        <div class="alert alert-warning">You don't have an active lease on file. Contact the office if this looks wrong.</div>
    @endif

    <div class="row g-3">
        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-header bg-white fw-semibold d-flex justify-content-between align-items-center">
                    Payment history
                    <a href="{{ route('payments.index') }}" class="small fw-normal text-decoration-none">View all</a>
                </div>
                <div class="table-responsive">
                    <table class="table mb-0 align-middle">
                        <thead><tr><th>Amount</th><th>Date</th><th>Status</th><th></th></tr></thead>
                        <tbody>
                        @forelse($payments->take(6) as $p)
                            <tr>
                                <td class="text-nowrap fw-semibold">{{ number_format($p->amount) }} XAF</td>
                                <td class="text-nowrap">{{ $p->paid_on->format('d M Y') }}</td>
                                <td><span class="badge-soft badge-soft--{{ $tone[$p->status] }}">{{ ucfirst($p->status) }}</span></td>
                                <td class="text-end">
                                    @if($p->isApproved())
                                        <a href="{{ route('payments.receipt', $p) }}" class="small">Receipt</a>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="text-muted text-center py-4">No payments yet</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-header bg-white fw-semibold d-flex justify-content-between align-items-center">
                    Your reported issues
                    <a href="{{ route('issues.create') }}" class="small fw-normal text-decoration-none">+ Report new</a>
                </div>
                <div class="table-responsive">
                    <table class="table mb-0">
                        <thead><tr><th>Title</th><th>Priority</th><th>Status</th></tr></thead>
                        <tbody>
                        @forelse($issues->take(6) as $i)
                            <tr>
                                <td><a href="{{ route('issues.show', $i) }}">{{ $i->title }}</a></td>
                                <td>{{ ucfirst($i->priority) }}</td>
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
