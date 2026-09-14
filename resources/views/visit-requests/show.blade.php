@extends('layouts.app')
@section('title', 'Visit · '.$visitRequest->name)
@section('content')
    @php
        $vr = $visitRequest;
        $statusTone = ['new' => 'info', 'confirmed' => 'success', 'completed' => 'neutral', 'cancelled' => 'danger'];
        $open = in_array($vr->status, ['new', 'confirmed'], true);
    @endphp

    <div class="page-head">
        <div>
            <a href="{{ route('visit-requests.index') }}" class="small text-muted text-decoration-none"><i class="bi bi-arrow-left"></i> Visit requests</a>
            <h3 class="mt-1">{{ $vr->name }}</h3>
            <p class="page-head__sub">
                VR-{{ str_pad($vr->id, 5, '0', STR_PAD_LEFT) }} · requested {{ $vr->created_at->diffForHumans() }} ·
                <span class="badge-soft badge-soft--{{ $statusTone[$vr->status] ?? 'neutral' }}">{{ \App\Models\VisitRequest::STATUSES[$vr->status] ?? ucfirst($vr->status) }}</span>
            </p>
        </div>
        @if($open)
            <div class="d-flex gap-2 flex-wrap">
                @foreach(array_filter(['confirmed' => $vr->status === 'new', 'completed' => true, 'cancelled' => true]) as $status => $_)
                    <form method="POST" action="{{ route('visit-requests.update-status', $vr) }}"
                          @if($status === 'cancelled') onsubmit="return confirm('Cancel this visit?');" @endif>
                        @csrf @method('PUT')
                        <input type="hidden" name="status" value="{{ $status }}">
                        @if($status === 'confirmed')
                            <button class="btn btn-dark btn-sm"><i class="bi bi-check2"></i> Confirm visit</button>
                        @elseif($status === 'completed')
                            <button class="btn btn-outline-dark btn-sm"><i class="bi bi-check2-all"></i> Mark completed</button>
                        @else
                            <button class="btn btn-outline-danger btn-sm">Cancel</button>
                        @endif
                    </form>
                @endforeach
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
                            <div class="text-muted small">Scheduled visit</div>
                            @if($vr->visit_date)
                                <div class="fs-5 fw-bold">{{ $vr->visit_date->format('l d F Y') }}</div>
                                <div class="text-muted">at {{ $vr->visitTimeLabel() }}</div>
                                @unless($vr->visit_slot_id)
                                    <div class="small text-muted mt-1"><i class="bi bi-info-circle"></i> Proposed by the visitor (no visit dates were published)</div>
                                @endunless
                            @else
                                <div class="fs-5 fw-bold">Not scheduled</div>
                                <div class="text-muted small">Older request made before online booking.</div>
                            @endif
                        </div>
                    </div>
                    <hr class="my-4">
                    <dl class="row mb-0">
                        <dt class="col-sm-3 text-muted fw-normal small">Property</dt>
                        <dd class="col-sm-9"><a href="{{ route('properties.show', $vr->property) }}" class="fw-semibold text-decoration-none">{{ $vr->property->name }}</a><div class="small text-muted">{{ $vr->property->address }}@if($vr->property->city), {{ $vr->property->city }}@endif</div></dd>
                        <dt class="col-sm-3 text-muted fw-normal small">Email</dt>
                        <dd class="col-sm-9"><a href="mailto:{{ $vr->email }}">{{ $vr->email }}</a></dd>
                        <dt class="col-sm-3 text-muted fw-normal small">Phone</dt>
                        <dd class="col-sm-9"><a href="tel:{{ $vr->phone }}">{{ $vr->phone }}</a></dd>
                        <dt class="col-sm-3 text-muted fw-normal small">Message</dt>
                        <dd class="col-sm-9">{{ $vr->message ?? '—' }}</dd>
                        @if($vr->handledBy)
                            <dt class="col-sm-3 text-muted fw-normal small">Handled by</dt>
                            <dd class="col-sm-9 mb-0">{{ $vr->handledBy->name }}</dd>
                        @endif
                    </dl>
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="card h-100">
                <div class="card-header bg-white fw-semibold">Visit fee</div>
                <div class="card-body p-4">
                    @if($vr->payment_status === 'paid')
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <span class="fs-4 fw-bold">{{ number_format($vr->fee_amount) }} XAF</span>
                            <span class="badge-soft badge-soft--success"><i class="bi bi-check-lg"></i> Paid</span>
                        </div>
                        <dl class="row small mb-0">
                            <dt class="col-5 text-muted fw-normal">Method</dt><dd class="col-7">{{ $vr->paymentMethodLabel() }}</dd>
                            @if($vr->transaction_ref)
                                <dt class="col-5 text-muted fw-normal">Reference</dt><dd class="col-7 font-monospace">{{ $vr->transaction_ref }}</dd>
                            @endif
                            <dt class="col-5 text-muted fw-normal">Paid</dt><dd class="col-7 mb-0">{{ $vr->paid_at?->format('d M Y, H:i') }} · {{ $vr->payment_option === 'pay_now' ? 'online' : 'at the visit' }}</dd>
                        </dl>
                    @elseif($vr->payment_status === 'not_required')
                        <p class="text-muted mb-0">No fee for this visit.</p>
                    @else
                        <div class="d-flex justify-content-between align-items-baseline mb-3">
                            <span class="text-muted small">To collect at the visit</span>
                            <span class="fs-4 fw-bold">{{ number_format($vr->fee_amount) }} XAF</span>
                        </div>
                        @if($vr->status !== 'cancelled')
                            <div x-data="{ via: 'mobile' }">
                                <div class="btn-group w-100 mb-3" role="group" aria-label="Collection method">
                                    <button type="button" class="btn btn-sm" :class="via === 'mobile' ? 'btn-dark' : 'btn-outline-dark'" @click="via = 'mobile'">Mobile money</button>
                                    <button type="button" class="btn btn-sm" :class="via === 'cash' ? 'btn-dark' : 'btn-outline-dark'" @click="via = 'cash'">Cash</button>
                                </div>
                                <form method="POST" action="{{ route('visit-requests.collect-fee', $vr) }}" x-show="via === 'mobile'">
                                    @csrf
                                    <x-mobile-money-pay :amount="$vr->fee_amount" :phone="$vr->phone" label="Collect" />
                                </form>
                                <form method="POST" action="{{ route('visit-requests.collect-fee', $vr) }}" x-show="via === 'cash'" x-cloak>
                                    @csrf
                                    <input type="hidden" name="method" value="cash">
                                    <button class="btn btn-accent w-100 py-2"><i class="bi bi-cash-coin me-1"></i> Record {{ number_format($vr->fee_amount) }} XAF in cash</button>
                                </form>
                            </div>
                        @else
                            <p class="text-muted small mb-0">This visit was cancelled.</p>
                        @endif
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
