@extends('layouts.app')
@section('title', 'Contract · '.$lease->tenant->name)
@section('content')
    @php $tone = ['draft' => 'neutral', 'sent' => 'warning', 'signed' => 'success'][$contract->status]; @endphp

    <div class="page-head">
        <div>
            <a href="{{ route('properties.show', $lease->property) }}" class="small text-muted text-decoration-none"><i class="bi bi-arrow-left"></i> {{ $lease->property->name }}</a>
            <h3 class="mt-1">{{ $contract->exists ? 'Edit contract' : 'Prepare contract' }}</h3>
            <p class="page-head__sub">{{ $lease->tenant->name }} · <span class="badge-soft badge-soft--{{ $tone }}">{{ $contract->exists ? $contract->statusLabel() : 'New' }}</span></p>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-4 order-lg-2">
            <div class="card sticky-lg-top" style="top: 76px;">
                <div class="card-body p-4">
                    <form method="POST" action="{{ route('contracts.store', $lease) }}"
                          x-data="{ conditions: @js(old('special_conditions', $contract->special_conditions ?? '')) }"
                          x-effect="const s = document.querySelector('[data-special-conditions]'); if (s) { s.hidden = !conditions.trim(); s.querySelector('[data-special-conditions-text]').textContent = conditions; }">
                        @csrf
                        <dl class="row small mb-3">
                            <dt class="col-5 text-muted fw-normal">Rent</dt><dd class="col-7">{{ number_format($lease->rent_amount) }} XAF / {{ $lease->billing_cycle }}</dd>
                            <dt class="col-5 text-muted fw-normal">Starts</dt><dd class="col-7">{{ $lease->start_date->format('d M Y') }}</dd>
                            <dt class="col-5 text-muted fw-normal">Ends</dt><dd class="col-7 mb-0">{{ $lease->end_date?->format('d M Y') ?? 'Open-ended' }}</dd>
                        </dl>

                        <label class="form-label" for="special_conditions">Special conditions</label>
                        <textarea name="special_conditions" id="special_conditions" rows="7" class="form-control" x-model="conditions"
                                  placeholder="e.g. Security deposit of two months' rent, refundable at the end of the lease."></textarea>
                        <div class="form-text mb-3">Optional. Everything else is generated from the lease and property.</div>

                        @if($contract->isSent())
                            <div class="alert alert-warning small py-2">Sent {{ $contract->sent_at->diffForHumans() }}. Saving sends the updated version to the tenant again.</div>
                            <button name="send" value="1" class="btn btn-dark w-100"><i class="bi bi-send me-1"></i> Update & resend</button>
                        @else
                            <button name="send" value="1" class="btn btn-dark w-100"><i class="bi bi-send me-1"></i> Send to tenant for signature</button>
                            <button class="btn btn-outline-dark w-100 mt-2">Save draft</button>
                        @endif
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-8 order-lg-1">
            <div class="text-muted small mb-2"><i class="bi bi-eye me-1"></i> Preview</div>
            <div class="card">
                <div class="contract-doc">{!! $preview !!}</div>
            </div>
        </div>
    </div>
@endsection
