@extends('layouts.app')
@section('title', $property->name)
@section('content')
    @include('partials.gallery', ['images' => $property->images, 'alt' => $property->name])

    <div class="d-flex justify-content-between align-items-start mb-3">
        <div>
            <h3 class="mb-1">{{ $property->name }}</h3>
            <div class="text-muted">{{ $property->address }}@if($property->city), {{ $property->city }}@endif</div>
        </div>
        @if(auth()->user()->isAdmin())
        <div class="d-flex gap-2">
            <a href="{{ route('properties.edit', $property) }}" class="btn btn-outline-dark btn-sm">Edit</a>
            <form method="POST" action="{{ route('properties.destroy', $property) }}" onsubmit="return confirm('Delete this property? This cannot be undone.');">
                @csrf @method('DELETE')
                <button class="btn btn-outline-danger btn-sm">Delete</button>
            </form>
        </div>
        @endif
    </div>

    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="card"><div class="card-body">
                <div class="text-muted small">Status</div>
                @php $badge = ['vacant'=>'bg-warning text-dark','occupied'=>'bg-success','maintenance'=>'bg-secondary']; @endphp
                <span class="badge {{ $badge[$property->status] }}">{{ ucfirst($property->status) }}</span>
            </div></div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card"><div class="card-body">
                <div class="text-muted small">Monthly rent</div>
                <div class="fw-semibold">{{ number_format($property->monthly_rent) }} XAF</div>
            </div></div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card"><div class="card-body">
                <div class="text-muted small">Owner</div>
                <div class="fw-semibold">{{ $property->owner->name }}</div>
            </div></div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card"><div class="card-body">
                <div class="text-muted small">Type</div>
                <div class="fw-semibold">{{ ucfirst($property->type) }}</div>
            </div></div>
        </div>
    </div>

    @php $active = $property->leases->firstWhere('status', 'active'); @endphp

    <div class="card mb-4">
        <div class="card-header bg-white fw-semibold d-flex justify-content-between align-items-center">
            Current lease
            @if(auth()->user()->isAdmin() && !$active)
                <a href="{{ route('leases.create', $property) }}" class="btn btn-sm btn-dark">Assign tenant</a>
            @endif
        </div>
        <div class="card-body">
            @if($active)
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <div>
                        <div class="fw-semibold">{{ $active->tenant->name }}</div>
                        <div class="text-muted small">
                            Since {{ $active->start_date->format('d M Y') }} ·
                            {{ number_format($active->rent_amount) }} XAF / {{ $active->billing_cycle }}
                        </div>
                    </div>
                    <div class="d-flex gap-2">
                        @if(auth()->user()->isAdmin())
                        <a href="{{ route('payments.create', $active) }}" class="btn btn-sm btn-outline-dark">Record payment</a>
                        <form method="POST" action="{{ route('leases.end', $active) }}" onsubmit="return confirm('End this lease and mark the property vacant?');">
                            @csrf
                            <button class="btn btn-sm btn-outline-danger">End lease</button>
                        </form>
                        @endif
                    </div>
                </div>
            @else
                <p class="text-muted mb-0">No active tenant on this property.</p>
            @endif
        </div>
    </div>

    <div class="row g-3">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-white fw-semibold">Lease & payment history</div>
                <div class="table-responsive">
                    <table class="table mb-0">
                        <thead><tr><th>Tenant</th><th>Period</th><th>Status</th></tr></thead>
                        <tbody>
                        @forelse($property->leases as $lease)
                            <tr>
                                <td>{{ $lease->tenant->name }}</td>
                                <td>{{ $lease->start_date->format('M Y') }} – {{ $lease->end_date?->format('M Y') ?? 'present' }}</td>
                                <td><span class="badge bg-{{ $lease->status=='active'?'success':'secondary' }}">{{ ucfirst($lease->status) }}</span></td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="text-muted text-center py-3">No leases yet</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-white fw-semibold">Issues</div>
                <div class="table-responsive">
                    <table class="table mb-0">
                        <thead><tr><th>Title</th><th>Priority</th><th>Status</th></tr></thead>
                        <tbody>
                        @forelse($property->issues as $issue)
                            <tr>
                                <td><a href="{{ route('issues.show', $issue) }}">{{ $issue->title }}</a></td>
                                <td>{{ ucfirst($issue->priority) }}</td>
                                <td><span class="badge bg-secondary">{{ ucfirst(str_replace('_',' ',$issue->status)) }}</span></td>
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

    @if(auth()->user()->isAdmin())
    <div class="card mt-3">
        <div class="card-header bg-white fw-semibold">Photos</div>
        <div class="card-body">
            <livewire:property-image-manager :property="$property" :key="'images-'.$property->id" />
        </div>
    </div>
    @endif

    @if(auth()->user()->isAdmin() || auth()->user()->isOwner())
    <div class="card mt-3">
        <div class="card-header bg-white fw-semibold">Visit requests</div>
        <div class="table-responsive">
            <table class="table mb-0 align-middle">
                <thead><tr><th>Name</th><th>Contact</th><th>Message</th><th>Status</th>@if(auth()->user()->isAdmin())<th></th>@endif</tr></thead>
                <tbody>
                @forelse($property->visitRequests as $vr)
                    <tr>
                        <td>{{ $vr->name }}</td>
                        <td class="small">
                            <a href="mailto:{{ $vr->email }}">{{ $vr->email }}</a><br>
                            <a href="tel:{{ $vr->phone }}">{{ $vr->phone }}</a>
                        </td>
                        <td class="small">{{ $vr->message ?? '—' }}</td>
                        <td>
                            @php $vbadge = ['new'=>'bg-warning text-dark','contacted'=>'bg-info text-dark','closed'=>'bg-secondary']; @endphp
                            <span class="badge {{ $vbadge[$vr->status] }}">{{ ucfirst($vr->status) }}</span>
                        </td>
                        @if(auth()->user()->isAdmin())
                        <td>
                            @if($vr->status !== 'closed')
                            <form method="POST" action="{{ route('visit-requests.update-status', $vr) }}" class="d-flex gap-1">
                                @csrf @method('PUT')
                                <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                                    <option value="new" {{ $vr->status=='new'?'selected':'' }}>New</option>
                                    <option value="contacted" {{ $vr->status=='contacted'?'selected':'' }}>Contacted</option>
                                    <option value="closed" {{ $vr->status=='closed'?'selected':'' }}>Closed</option>
                                </select>
                            </form>
                            @endif
                        </td>
                        @endif
                    </tr>
                @empty
                    <tr><td colspan="{{ auth()->user()->isAdmin() ? 5 : 4 }}" class="text-muted text-center py-3">No visit requests yet</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @endif
@endsection
