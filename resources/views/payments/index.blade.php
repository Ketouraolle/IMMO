@extends('layouts.app')
@section('title', 'Payments')
@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3 class="mb-0">Payments</h3>
        @if(auth()->user()->isTenant())
        <a href="{{ route('payments.submit-form') }}" class="btn btn-dark btn-sm">+ Submit a payment</a>
        @endif
    </div>

    <div class="mb-3 btn-group">
        <a href="{{ route('payments.index') }}" class="btn btn-sm btn-outline-secondary {{ request('status') ? '' : 'active' }}">All</a>
        <a href="{{ route('payments.index', ['status'=>'pending']) }}" class="btn btn-sm btn-outline-secondary {{ request('status')=='pending' ? 'active' : '' }}">Pending</a>
        <a href="{{ route('payments.index', ['status'=>'approved']) }}" class="btn btn-sm btn-outline-secondary {{ request('status')=='approved' ? 'active' : '' }}">Approved</a>
        <a href="{{ route('payments.index', ['status'=>'rejected']) }}" class="btn btn-sm btn-outline-secondary {{ request('status')=='rejected' ? 'active' : '' }}">Rejected</a>
    </div>

    <div class="card">
        <div class="table-responsive">
            <table class="table mb-0 align-middle">
                <thead><tr><th>Receipt #</th><th>Tenant</th><th>Property</th><th>Amount</th><th>Date</th><th>Method</th><th>Status</th><th></th></tr></thead>
                <tbody>
                @forelse($payments as $p)
                    <tr>
                        <td>{{ $p->receipt_number ?? '—' }}</td>
                        <td>{{ $p->lease->tenant->name }}</td>
                        <td>{{ $p->lease->property->name }}</td>
                        <td>{{ number_format($p->amount) }} XAF</td>
                        <td>{{ $p->paid_on->format('d M Y') }}</td>
                        <td>{{ ucfirst(str_replace('_',' ',$p->method)) }}</td>
                        <td>
                            @php $badge = ['pending'=>'bg-warning text-dark','approved'=>'bg-success','rejected'=>'bg-danger']; @endphp
                            <span class="badge {{ $badge[$p->status] }}">{{ ucfirst($p->status) }}</span>
                            @if($p->status=='rejected' && $p->rejection_reason)
                                <div class="small text-muted">{{ $p->rejection_reason }}</div>
                            @endif
                        </td>
                        <td class="text-end">
                            @if($p->status=='approved')
                                <a href="{{ route('payments.receipt', $p) }}" class="btn btn-sm btn-outline-dark">Receipt</a>
                            @elseif($p->status=='pending' && auth()->user()->isAdmin())
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
                                        <input type="text" name="rejection_reason" class="form-control form-control-sm" placeholder="Reason for rejection" required>
                                        <button class="btn btn-sm btn-danger">Confirm</button>
                                    </form>
                                </div>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="text-muted text-center py-4">No payments recorded yet</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="mt-3">{{ $payments->links() }}</div>
@endsection
