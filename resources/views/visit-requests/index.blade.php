@extends('layouts.app')
@section('title', __('Visit requests'))
@section('content')
    @php
        $statusTone = ['new' => 'info', 'confirmed' => 'success', 'completed' => 'neutral', 'cancelled' => 'danger'];
        $filter = fn (array $params) => route('visit-requests.index', array_filter(array_merge(request()->except('page'), $params)));
    @endphp

    <div class="page-head">
        <div>
            <h3>{{ __('Visit requests') }}</h3>
            <p class="page-head__sub">{{ __('Visits booked from the public listings.') }}</p>
        </div>
    </div>

    <div class="d-flex flex-wrap gap-2 justify-content-between mb-3">
        <div class="pill-nav">
            <a href="{{ $filter(['status' => null]) }}" class="{{ request('status') ? '' : 'active' }}">{{ __('All') }}</a>
            @foreach(\App\Models\VisitRequest::STATUSES as $value => $label)
                <a href="{{ $filter(['status' => $value]) }}" class="{{ request('status') === $value ? 'active' : '' }}">{{ __($label) }}</a>
            @endforeach
        </div>
        <div class="pill-nav">
            <a href="{{ $filter(['payment' => null]) }}" class="{{ request('payment') ? '' : 'active' }}">{{ __('Any fee') }}</a>
            <a href="{{ $filter(['payment' => 'unpaid']) }}" class="{{ request('payment') === 'unpaid' ? 'active' : '' }}">{{ __('To collect') }}</a>
            <a href="{{ $filter(['payment' => 'paid']) }}" class="{{ request('payment') === 'paid' ? 'active' : '' }}">{{ __('Paid') }}</a>
        </div>
    </div>

    <div class="card">
        <div class="table-responsive">
            <table class="table mb-0 align-middle">
                <thead><tr><th>{{ __('Visitor') }}</th><th>{{ __('Property') }}</th><th>{{ __('Visit') }}</th><th>{{ __('Fee') }}</th><th>{{ __('Status') }}</th><th></th></tr></thead>
                <tbody>
                @forelse($visitRequests as $vr)
                    <tr>
                        <td>
                            <a href="{{ route('visit-requests.show', $vr) }}">{{ $vr->name }}</a>
                            <div class="small text-muted">{{ $vr->phone }}</div>
                        </td>
                        <td>{{ $vr->property->name }}</td>
                        <td>
                            @if($vr->visit_date)
                                <div class="fw-semibold">{{ ucfirst($vr->visit_date->translatedFormat('D j M')) }}</div>
                                <div class="small text-muted">{{ $vr->visitTimeLabel() }}</div>
                            @else
                                <span class="small text-muted">{{ __('Not scheduled') }}</span>
                            @endif
                        </td>
                        <td>@include('visit-requests._payment-badge', ['vr' => $vr])</td>
                        <td><span class="badge-soft badge-soft--{{ $statusTone[$vr->status] ?? 'neutral' }}">{{ $vr->statusLabel() }}</span></td>
                        <td class="text-end"><a href="{{ route('visit-requests.show', $vr) }}" class="btn btn-sm btn-outline-dark">{{ __('View') }}</a></td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-muted text-center py-5">{{ __('No visit requests match these filters') }}</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="mt-3">{{ $visitRequests->links() }}</div>
@endsection
