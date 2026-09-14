@extends('layouts.app')
@section('title', __('Visit · :name', ['name' => $visitRequest->name]))
@section('content')
    @php
        $vr = $visitRequest;
        $statusTone = ['new' => 'info', 'confirmed' => 'success', 'completed' => 'neutral', 'cancelled' => 'danger'];
        $open = in_array($vr->status, ['new', 'confirmed'], true);
    @endphp

    <div class="page-head">
        <div>
            <a href="{{ route('visit-requests.index') }}" class="back-to"><i class="bi bi-arrow-left"></i> {{ __('Visit requests') }}</a>
            <h3 class="mt-1">{{ $vr->name }}</h3>
            <p class="page-head__sub">
                VR-{{ str_pad($vr->id, 5, '0', STR_PAD_LEFT) }} · {{ __('requested :time', ['time' => $vr->created_at->diffForHumans()]) }} ·
                <span class="badge-soft badge-soft--{{ $statusTone[$vr->status] ?? 'neutral' }}">{{ $vr->statusLabel() }}</span>
            </p>
        </div>
        @if($open)
            <div class="d-flex gap-2 flex-wrap">
                @if($vr->status === 'new')
                    <form method="POST" action="{{ route('visit-requests.update-status', $vr) }}">
                        @csrf @method('PUT')
                        <input type="hidden" name="status" value="confirmed">
                        <button class="btn btn-dark btn-sm"><i class="bi bi-check2"></i> {{ __('Confirm visit') }}</button>
                    </form>
                @endif
                <form method="POST" action="{{ route('visit-requests.update-status', $vr) }}">
                    @csrf @method('PUT')
                    <input type="hidden" name="status" value="completed">
                    <button class="btn btn-outline-dark btn-sm"><i class="bi bi-check2-all"></i> {{ __('Mark completed') }}</button>
                </form>
                <form method="POST" action="{{ route('visit-requests.update-status', $vr) }}" data-confirm="{{ __('Cancel this visit?') }}" onsubmit="return confirm(this.dataset.confirm);">
                    @csrf @method('PUT')
                    <input type="hidden" name="status" value="cancelled">
                    <button class="btn btn-outline-danger btn-sm">{{ __('Cancel') }}</button>
                </form>
            </div>
        @endif
    </div>

    <div class="row g-3">
        <div class="col-lg-7">
            <div class="card h-100">
                <div class="card-body p-4">
                    <div class="d-flex gap-3 align-items-center">
                        <span class="stat-icon" style="width:3.2rem;height:3.2rem;font-size:1.35rem;"><i class="bi bi-calendar2-event"></i></span>
                        <div>
                            <div class="text-muted small">{{ __('Scheduled visit') }}</div>
                            @if($vr->visit_date)
                                <div class="fs-5 fw-bold">{{ ucfirst($vr->visit_date->translatedFormat('l j F Y')) }}</div>
                                <div class="text-muted">{{ __('at :time', ['time' => $vr->visitTimeLabel()]) }}</div>
                                @unless($vr->visit_slot_id)
                                    <div class="small text-muted mt-1"><i class="bi bi-info-circle"></i> {{ __('Proposed by the visitor (no visit dates were published)') }}</div>
                                @endunless
                            @else
                                <div class="fs-5 fw-bold">{{ __('Not scheduled') }}</div>
                                <div class="text-muted small">{{ __('Older request made before online booking.') }}</div>
                            @endif
                        </div>
                    </div>
                    <hr class="my-4">
                    <dl class="row mb-0">
                        <dt class="col-sm-3 text-muted fw-normal small">{{ __('Property') }}</dt>
                        <dd class="col-sm-9"><a href="{{ route('properties.show', $vr->property) }}" class="fw-semibold text-decoration-none">{{ $vr->property->name }}</a><div class="small text-muted">{{ $vr->property->address }}@if($vr->property->city), {{ $vr->property->city }}@endif</div></dd>
                        <dt class="col-sm-3 text-muted fw-normal small">{{ __('Email') }}</dt>
                        <dd class="col-sm-9"><a href="mailto:{{ $vr->email }}">{{ $vr->email }}</a></dd>
                        <dt class="col-sm-3 text-muted fw-normal small">{{ __('Phone') }}</dt>
                        <dd class="col-sm-9"><a href="tel:{{ $vr->phone }}">{{ $vr->phone }}</a></dd>
                        <dt class="col-sm-3 text-muted fw-normal small">{{ __('Message') }}</dt>
                        <dd class="col-sm-9">{{ $vr->message ?? '—' }}</dd>
                        @if($vr->handledBy)
                            <dt class="col-sm-3 text-muted fw-normal small">{{ __('Handled by') }}</dt>
                            <dd class="col-sm-9 mb-0">{{ $vr->handledBy->name }}</dd>
                        @endif
                    </dl>
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="card h-100">
                <div class="card-header fw-semibold">{{ __('Visit fee') }}</div>
                <div class="card-body p-4">
                    @if($vr->payment_status === 'paid')
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <span class="fs-4 fw-bold">{{ number_format($vr->fee_amount) }} XAF</span>
                            <span class="badge-soft badge-soft--success"><i class="bi bi-check-lg"></i> {{ __('Paid') }}</span>
                        </div>
                        <dl class="row small mb-0">
                            <dt class="col-5 text-muted fw-normal">{{ __('Method') }}</dt><dd class="col-7">{{ $vr->paymentMethodLabel() }}</dd>
                            @if($vr->transaction_ref)
                                <dt class="col-5 text-muted fw-normal">{{ __('Reference') }}</dt><dd class="col-7 font-monospace">{{ $vr->transaction_ref }}</dd>
                            @endif
                            <dt class="col-5 text-muted fw-normal">{{ __('Paid') }}</dt>
                            <dd class="col-7 mb-0">{{ $vr->paid_at?->translatedFormat('d M Y, H:i') }} · {{ $vr->payment_option === 'pay_now' ? __('online') : __('at the visit') }}</dd>
                        </dl>
                    @elseif($vr->payment_status === 'not_required')
                        <p class="text-muted mb-0">{{ __('No fee for this visit.') }}</p>
                    @else
                        <div class="d-flex justify-content-between align-items-baseline mb-3">
                            <span class="text-muted small">{{ __('To collect at the visit') }}</span>
                            <span class="fs-4 fw-bold">{{ number_format($vr->fee_amount) }} XAF</span>
                        </div>
                        @if($vr->status !== 'cancelled')
                            <div x-data="{ via: 'mobile' }">
                                <div class="btn-group w-100 mb-3" role="group" aria-label="{{ __('Collection method') }}">
                                    <button type="button" class="btn btn-sm" :class="via === 'mobile' ? 'btn-dark' : 'btn-outline-dark'" @click="via = 'mobile'">{{ __('Mobile money') }}</button>
                                    <button type="button" class="btn btn-sm" :class="via === 'cash' ? 'btn-dark' : 'btn-outline-dark'" @click="via = 'cash'">{{ __('Cash') }}</button>
                                </div>
                                <form method="POST" action="{{ route('visit-requests.collect-fee', $vr) }}" x-show="via === 'mobile'">
                                    @csrf
                                    <x-mobile-money-pay :amount="$vr->fee_amount" :phone="$vr->phone" :label="__('Collect')" />
                                </form>
                                <form method="POST" action="{{ route('visit-requests.collect-fee', $vr) }}" x-show="via === 'cash'" x-cloak>
                                    @csrf
                                    <input type="hidden" name="method" value="cash">
                                    <button class="btn btn-accent w-100 py-2"><i class="bi bi-cash-coin me-1"></i> {{ __('Record :amount XAF in cash', ['amount' => number_format($vr->fee_amount)]) }}</button>
                                </form>
                            </div>
                        @else
                            <p class="text-muted small mb-0">{{ __('This visit was cancelled.') }}</p>
                        @endif
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
