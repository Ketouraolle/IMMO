@extends('layouts.app')
@section('title', __('Properties'))
@section('content')
    @php $statusTone = ['vacant' => 'warning', 'occupied' => 'success', 'maintenance' => 'neutral']; @endphp

    <div class="page-head">
        <div><h3>{{ auth()->user()->isOwner() ? __('My properties') : __('Properties') }}</h3></div>
        @if(auth()->user()->isAdmin())
            <a href="{{ route('properties.create') }}" class="btn btn-dark btn-sm"><i class="bi bi-plus-lg"></i> {{ __('Add property') }}</a>
        @endif
    </div>

    <div class="card">
        <div class="table-responsive">
            <table class="table mb-0 align-middle">
                <thead><tr><th></th><th>{{ __('Name') }}</th><th>{{ __('City') }}</th><th>{{ __('Owner') }}</th><th>{{ __('Rent') }}</th><th>{{ __('Status') }}</th><th>{{ __('Tenant') }}</th></tr></thead>
                <tbody>
                @forelse($properties as $p)
                    <tr>
                        <td style="width:56px;">
                            <a href="{{ route('properties.show', $p) }}">
                                @if($p->mainImage)
                                    <img src="{{ asset('storage/' . $p->mainImage->path) }}" class="rounded" style="width:44px;height:44px;object-fit:cover;" alt="">
                                @else
                                    <div class="rounded bg-body-secondary d-flex align-items-center justify-content-center text-muted" style="width:44px;height:44px;font-size:.9rem;"><i class="bi bi-image"></i></div>
                                @endif
                            </a>
                        </td>
                        <td><a href="{{ route('properties.show', $p) }}">{{ $p->name }}</a></td>
                        <td>{{ $p->city ?? '—' }}</td>
                        <td>{{ $p->owner->name }}</td>
                        <td class="text-nowrap">{{ number_format($p->monthly_rent) }} XAF</td>
                        <td><span class="badge-soft badge-soft--{{ $statusTone[$p->status] }}">{{ __(ucfirst($p->status)) }}</span></td>
                        <td>{{ $p->activeLease?->tenant?->name ?? '—' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-muted text-center py-5">{{ __('No properties yet') }}</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="mt-3">{{ $properties->links() }}</div>
@endsection
