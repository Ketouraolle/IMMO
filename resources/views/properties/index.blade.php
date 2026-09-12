@extends('layouts.app')
@section('title', 'Properties')
@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3 class="mb-0">Properties</h3>
        @if(auth()->user()->isAdmin())
        <a href="{{ route('properties.create') }}" class="btn btn-dark btn-sm">+ Add property</a>
        @endif
    </div>

    <div class="card">
        <div class="table-responsive">
            <table class="table mb-0 align-middle">
                <thead><tr><th></th><th>Name</th><th>City</th><th>Owner</th><th>Rent</th><th>Status</th><th>Tenant</th></tr></thead>
                <tbody>
                @forelse($properties as $p)
                    <tr>
                        <td style="width:56px;">
                            <a href="{{ route('properties.show', $p) }}">
                                @if($p->mainImage)
                                    <img src="{{ asset('storage/' . $p->mainImage->path) }}" class="rounded" style="width:44px;height:44px;object-fit:cover;" alt="">
                                @else
                                    <div class="rounded bg-body-secondary d-flex align-items-center justify-content-center text-muted" style="width:44px;height:44px;font-size:.7rem;">—</div>
                                @endif
                            </a>
                        </td>
                        <td><a href="{{ route('properties.show', $p) }}">{{ $p->name }}</a></td>
                        <td>{{ $p->city ?? '—' }}</td>
                        <td>{{ $p->owner->name }}</td>
                        <td>{{ number_format($p->monthly_rent) }} XAF</td>
                        <td>
                            @php
                                $badge = ['vacant'=>'bg-warning text-dark','occupied'=>'bg-success','maintenance'=>'bg-secondary'];
                            @endphp
                            <span class="badge {{ $badge[$p->status] }}">{{ ucfirst($p->status) }}</span>
                        </td>
                        <td>{{ $p->activeLease?->tenant?->name ?? '—' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-muted text-center py-4">No properties yet</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="mt-3">{{ $properties->links() }}</div>
@endsection
