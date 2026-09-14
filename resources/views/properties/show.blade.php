@extends('layouts.app')
@section('title', $property->name)
@section('content')
    @php
        $isAdmin = auth()->user()->isAdmin();
        $statusTone = ['vacant' => 'warning', 'occupied' => 'success', 'maintenance' => 'neutral'];
        $contractTone = ['draft' => 'neutral', 'sent' => 'warning', 'signed' => 'success'];
        $visitTone = ['new' => 'info', 'confirmed' => 'success', 'completed' => 'neutral', 'cancelled' => 'danger'];
        $active = $property->leases->firstWhere('status', 'active');
        $contract = $active?->contract;
        $rate = rtrim(rtrim(number_format($property->commission_rate, 2), '0'), '.');
    @endphp

    <div class="page-head">
        <div>
            <a href="{{ route('properties.index') }}" class="back-to"><i class="bi bi-arrow-left"></i> {{ __('Properties') }}</a>
            <h3 class="mt-1">{{ $property->name }}</h3>
            <p class="page-head__sub">
                <i class="bi bi-geo-alt"></i> {{ $property->address }}@if($property->city), {{ $property->city }}@endif
                · {{ __(ucfirst($property->type)) }}
                · <span class="badge-soft badge-soft--{{ $statusTone[$property->status] }}">{{ __(ucfirst($property->status)) }}</span>
            </p>
        </div>
        @if($isAdmin)
            <div class="d-flex gap-2">
                <a href="{{ route('public.properties.show', $property) }}" target="_blank" class="btn btn-outline-dark btn-sm"><i class="bi bi-box-arrow-up-right"></i> {{ __('Public page') }}</a>
                <a href="{{ route('properties.edit', $property) }}" class="btn btn-outline-dark btn-sm"><i class="bi bi-pencil"></i> {{ __('Edit') }}</a>
                <form method="POST" action="{{ route('properties.destroy', $property) }}" data-confirm="{{ __('Delete this property? This cannot be undone.') }}" onsubmit="return confirm(this.dataset.confirm);">
                    @csrf @method('DELETE')
                    <button class="btn btn-outline-danger btn-sm" title="{{ __('Delete property') }}" aria-label="{{ __('Delete property') }}"><i class="bi bi-trash3"></i></button>
                </form>
            </div>
        @endif
    </div>

    @include('partials.gallery', ['images' => $property->images, 'alt' => $property->name])

    <div class="row g-3 mb-4">
        @foreach([
            [__('Monthly rent'), number_format($property->monthly_rent).' XAF', 'bi-cash-stack'],
            [__('Owner'), $property->owner->name, 'bi-person'],
            [__('Visit fee'), $property->visit_fee > 0 ? number_format($property->visit_fee).' XAF' : __('Free'), 'bi-calendar2-check'],
            [__('Commission'), __(':rate% of rent', ['rate' => $rate]), 'bi-percent'],
        ] as [$label, $value, $icon])
            <div class="col-6 col-lg-3">
                <div class="card h-100">
                    <div class="card-body d-flex gap-3 align-items-center">
                        <span class="stat-icon"><i class="bi {{ $icon }}"></i></span>
                        <div class="min-w-0">
                            <div class="text-muted small">{{ $label }}</div>
                            <div class="fw-semibold text-truncate">{{ $value }}</div>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="card mb-4">
        <div class="card-header fw-semibold d-flex justify-content-between align-items-center">
            {{ __('Current lease') }}
            @if($isAdmin && ! $active)
                <a href="{{ route('leases.create', $property) }}" class="btn btn-sm btn-dark"><i class="bi bi-person-plus"></i> {{ __('Assign tenant') }}</a>
            @endif
        </div>
        <div class="card-body">
            @if($active)
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                    <div>
                        <div class="fw-semibold">{{ $active->tenant->name }}</div>
                        <div class="text-muted small">
                            {{ __('Since :date', ['date' => $active->start_date->translatedFormat('d M Y')]) }} ·
                            {{ number_format($active->rent_amount) }} XAF / {{ __($active->billing_cycle) }}
                        </div>
                        <div class="mt-2">
                            @if($contract)
                                <span class="badge-soft badge-soft--{{ $contractTone[$contract->status] }}"><i class="bi bi-file-earmark-text"></i> {{ __('Contract: :status', ['status' => $contract->statusLabel()]) }}</span>
                            @else
                                <span class="badge-soft badge-soft--neutral"><i class="bi bi-file-earmark"></i> {{ __('No contract yet') }}</span>
                            @endif
                        </div>
                    </div>
                    <div class="d-flex gap-2 flex-wrap">
                        @if($isAdmin)
                            @if(! $contract)
                                <a href="{{ route('contracts.create', $active) }}" class="btn btn-sm btn-dark"><i class="bi bi-file-earmark-plus"></i> {{ __('Generate contract') }}</a>
                            @elseif($contract->isDraft())
                                <a href="{{ route('contracts.create', $active) }}" class="btn btn-sm btn-dark"><i class="bi bi-send"></i> {{ __('Review & send contract') }}</a>
                            @else
                                <a href="{{ route('contracts.show', $contract) }}" class="btn btn-sm btn-outline-dark">{{ __('View contract') }}</a>
                            @endif
                            <a href="{{ route('payments.create', $active) }}" class="btn btn-sm btn-outline-dark">{{ __('Record payment') }}</a>
                            <form method="POST" action="{{ route('leases.end', $active) }}" data-confirm="{{ __('End this lease and mark the property vacant?') }}" onsubmit="return confirm(this.dataset.confirm);">
                                @csrf
                                <button class="btn btn-sm btn-outline-danger">{{ __('End lease') }}</button>
                            </form>
                        @elseif($contract && ! $contract->isDraft())
                            <a href="{{ route('contracts.show', $contract) }}" class="btn btn-sm btn-outline-dark">{{ __('View contract') }}</a>
                        @endif
                    </div>
                </div>
            @else
                <p class="text-muted mb-0">{{ __('No active tenant on this property.') }}</p>
            @endif
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-header fw-semibold">{{ __('Lease history') }}</div>
                <div class="table-responsive">
                    <table class="table mb-0">
                        <thead><tr><th>{{ __('Tenant') }}</th><th>{{ __('Period') }}</th><th>{{ __('Status') }}</th></tr></thead>
                        <tbody>
                        @forelse($property->leases as $lease)
                            <tr>
                                <td>{{ $lease->tenant->name }}</td>
                                <td>{{ $lease->start_date->translatedFormat('M Y') }} – {{ $lease->end_date?->translatedFormat('M Y') ?? __('present') }}</td>
                                <td><span class="badge-soft badge-soft--{{ $lease->status === 'active' ? 'success' : 'neutral' }}">{{ __(ucfirst($lease->status)) }}</span></td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="text-muted text-center py-3">{{ __('No leases yet') }}</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-header fw-semibold">{{ __('Issues') }}</div>
                <div class="table-responsive">
                    <table class="table mb-0">
                        <thead><tr><th>{{ __('Title') }}</th><th>{{ __('Priority') }}</th><th>{{ __('Status') }}</th></tr></thead>
                        <tbody>
                        @forelse($property->issues as $issue)
                            <tr>
                                <td><a href="{{ route('issues.show', $issue) }}">{{ $issue->title }}</a></td>
                                <td>{{ __(ucfirst($issue->priority)) }}</td>
                                <td><span class="badge-soft badge-soft--neutral">{{ __(ucfirst(str_replace('_', ' ', $issue->status))) }}</span></td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="text-muted text-center py-3">{{ __('No issues reported') }}</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    @if($isAdmin)
        <div class="row g-3 mb-4">
            <div class="col-lg-5">
                <div class="card h-100">
                    <div class="card-header fw-semibold d-flex justify-content-between align-items-center">
                        {{ __('Visit availability') }}
                        <a href="{{ route('properties.edit', $property) }}" class="small fw-normal text-decoration-none">
                            {{ $property->visit_fee > 0 ? __('Fee: :amount XAF', ['amount' => number_format($property->visit_fee)]) : __('Fee: free') }}
                        </a>
                    </div>
                    <div class="card-body">
                        <livewire:visit-availability-manager :property="$property" :key="'slots-'.$property->id" />
                    </div>
                </div>
            </div>
            <div class="col-lg-7">
                <div class="card h-100">
                    <div class="card-header fw-semibold">{{ __('Photos') }}</div>
                    <div class="card-body">
                        <livewire:property-image-manager :property="$property" :key="'images-'.$property->id" />
                    </div>
                </div>
            </div>
        </div>
    @endif

    @if($isAdmin || auth()->user()->isOwner())
        <div class="card">
            <div class="card-header fw-semibold">{{ __('Visit requests') }}</div>
            <div class="table-responsive">
                <table class="table mb-0 align-middle">
                    <thead><tr><th>{{ __('Visitor') }}</th><th>{{ __('Visit') }}</th><th>{{ __('Fee') }}</th><th>{{ __('Status') }}</th>@if($isAdmin)<th></th>@endif</tr></thead>
                    <tbody>
                    @forelse($property->visitRequests as $vr)
                        <tr>
                            <td>
                                {{ $vr->name }}
                                <div class="small text-muted">{{ $vr->phone }}</div>
                            </td>
                            <td>
                                @if($vr->visit_date)
                                    {{ ucfirst($vr->visit_date->translatedFormat('D j M')) }} <span class="text-muted small">· {{ $vr->visitTimeLabel() }}</span>
                                @else
                                    <span class="text-muted small">{{ __('Not scheduled') }}</span>
                                @endif
                            </td>
                            <td>@include('visit-requests._payment-badge', ['vr' => $vr])</td>
                            <td><span class="badge-soft badge-soft--{{ $visitTone[$vr->status] ?? 'neutral' }}">{{ $vr->statusLabel() }}</span></td>
                            @if($isAdmin)
                                <td class="text-end"><a href="{{ route('visit-requests.show', $vr) }}" class="btn btn-sm btn-outline-dark">{{ __('View') }}</a></td>
                            @endif
                        </tr>
                    @empty
                        <tr><td colspan="{{ $isAdmin ? 5 : 4 }}" class="text-muted text-center py-3">{{ __('No visit requests yet') }}</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @endif
@endsection
