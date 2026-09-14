@extends('layouts.app')
@section('title', 'Contract '.$contract->reference)
@section('content')
    @php
        $lease = $contract->lease;
        $user = auth()->user();
        $canSign = $user->isTenant() && $contract->isSent();
        $tone = ['draft' => 'neutral', 'sent' => 'warning', 'signed' => 'success'][$contract->status];
    @endphp

    <div class="page-head d-print-none">
        <div>
            @if($user->isAdmin())
                <a href="{{ route('properties.show', $lease->property) }}" class="small text-muted text-decoration-none"><i class="bi bi-arrow-left"></i> {{ $lease->property->name }}</a>
            @endif
            <h3 class="mt-1">Lease contract</h3>
            <p class="page-head__sub">{{ $lease->property->name }} · {{ $lease->tenant->name }} · <span class="badge-soft badge-soft--{{ $tone }}">{{ $contract->statusLabel() }}</span></p>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            @if($user->isAdmin() && ! $contract->isSigned())
                <a href="{{ route('contracts.create', $lease) }}" class="btn btn-outline-dark btn-sm"><i class="bi bi-pencil"></i> Edit</a>
                @if($contract->isDraft())
                    <form method="POST" action="{{ route('contracts.send', $contract) }}">
                        @csrf
                        <button class="btn btn-dark btn-sm"><i class="bi bi-send"></i> Send to tenant</button>
                    </form>
                @endif
            @endif
            @if($contract->isSigned())
                <button type="button" onclick="window.print()" class="btn btn-outline-dark btn-sm"><i class="bi bi-printer"></i> Print / Save as PDF</button>
            @endif
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-xl-9">
            @if($canSign)
                <div class="alert alert-info d-flex gap-2 align-items-center d-print-none">
                    <i class="bi bi-info-circle"></i> Read the contract carefully, then sign at the bottom of the page.
                </div>
            @elseif($user->isAdmin() && $contract->isSent())
                <div class="alert alert-warning d-flex gap-2 align-items-center d-print-none">
                    <i class="bi bi-hourglass-split"></i> Sent {{ $contract->sent_at->diffForHumans() }}, waiting for {{ $lease->tenant->name }} to sign.
                </div>
            @endif

            <div class="card mb-4">
                <div class="contract-doc">
                    {!! $document !!}

                    @if($contract->isSigned())
                        <h2>Signatures</h2>
                        <div class="row g-4">
                            <div class="col-sm-6">
                                <div class="small text-muted">Tenant</div>
                                <img src="{{ $contract->signature_data }}" class="signature-img d-block my-2" alt="Signature of {{ $lease->tenant->name }}">
                                <div class="fw-semibold">{{ $lease->tenant->name }}</div>
                                <div class="small text-muted">Signed electronically on {{ $contract->signed_at->format('d F Y \a\t H:i') }}</div>
                            </div>
                            <div class="col-sm-6">
                                <div class="small text-muted">For the landlord</div>
                                <div class="fw-semibold mt-2">EstateHub</div>
                                <div class="small text-muted">Issued by {{ $contract->creator?->name ?? 'EstateHub' }} on {{ $contract->sent_at->format('d F Y') }}</div>
                            </div>
                        </div>
                        <div class="mt-4 pt-3 border-top text-muted" style="font-size: .74rem; line-height: 1.6;">
                            Audit trail · Ref. {{ $contract->reference }} · Signed {{ $contract->signed_at->toIso8601String() }} from IP {{ $contract->signer_ip }}
                            · Document fingerprint (SHA-256) <span class="font-monospace">{{ substr($contract->body_hash, 0, 16) }}…</span>
                        </div>
                    @endif
                </div>
            </div>

            @if($canSign)
                <div class="card d-print-none" id="sign">
                    <div class="card-body p-4">
                        <h5 class="mb-1">Sign this contract</h5>
                        <p class="text-muted small mb-4">Your signature is tied to this exact version of the document.</p>

                        <form method="POST" action="{{ route('contracts.sign', $contract) }}" x-data="{ agreed: false, signing: false }" @submit="signing = true">
                            @csrf
                            <div x-data="signaturePad">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <label class="form-label mb-0">Draw your signature</label>
                                    <button type="button" class="btn btn-link btn-sm p-0 text-decoration-none" @click="clear()" x-show="hasInk" x-cloak>
                                        <i class="bi bi-arrow-counterclockwise"></i> Clear
                                    </button>
                                </div>
                                <div class="signature-pad">
                                    <div class="signature-pad__line"></div>
                                    <div class="signature-pad__hint" x-show="!hasInk">Sign here with your mouse, finger or stylus</div>
                                    <canvas x-ref="canvas" @pointerdown.prevent="down($event)" @pointermove="move($event)" @pointerup="up()" @pointercancel="up()"></canvas>
                                </div>
                                <input type="hidden" name="signature" x-ref="output">
                                @error('signature') <div class="text-danger small mt-2">{{ $message }}</div> @enderror

                                <div class="form-check mt-3">
                                    <input class="form-check-input" type="checkbox" name="agree" value="1" id="agree" x-model="agreed">
                                    <label class="form-check-label" for="agree">I, {{ $lease->tenant->name }}, have read and agree to the terms of this contract.</label>
                                </div>
                                @error('agree') <div class="text-danger small mt-1">{{ $message }}</div> @enderror

                                <button class="btn btn-dark w-100 py-2 mt-4" :disabled="!agreed || !hasInk || signing">
                                    <span x-show="!signing"><i class="bi bi-pen me-1"></i> Sign contract</span>
                                    <span x-show="signing" x-cloak><span class="spinner-border spinner-border-sm me-1"></span> Signing…</span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            @endif
        </div>
    </div>
@endsection
