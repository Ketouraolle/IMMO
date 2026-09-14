@extends('layouts.app')
@section('title', __('Contract · :name', ['name' => $lease->tenant->name]))
@section('content')
    @php $tone = ['draft' => 'neutral', 'sent' => 'warning', 'signed' => 'success'][$contract->status]; @endphp

    <div class="page-head">
        <div>
            <a href="{{ route('properties.show', $lease->property) }}" class="back-to"><i class="bi bi-arrow-left"></i> {{ $lease->property->name }}</a>
            <h3 class="mt-1">{{ $contract->exists ? __('Edit contract') : __('Prepare contract') }}</h3>
            <p class="page-head__sub">{{ $lease->tenant->name }} · <span class="badge-soft badge-soft--{{ $tone }}">{{ $contract->exists ? $contract->statusLabel() : __('New') }}</span></p>
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
                            <dt class="col-5 text-muted fw-normal">{{ __('Rent') }}</dt><dd class="col-7">{{ number_format($lease->rent_amount) }} XAF / {{ __($lease->billing_cycle) }}</dd>
                            <dt class="col-5 text-muted fw-normal">{{ __('Starts') }}</dt><dd class="col-7">{{ $lease->start_date->translatedFormat('d M Y') }}</dd>
                            <dt class="col-5 text-muted fw-normal">{{ __('Ends') }}</dt><dd class="col-7">{{ $lease->end_date?->translatedFormat('d M Y') ?? __('Open-ended') }}</dd>
                            <dt class="col-5 text-muted fw-normal">{{ __('Language') }}</dt><dd class="col-7 mb-0">{{ \App\Support\Locale::SUPPORTED[$contract->documentLocale()] ?? '' }} <span class="text-muted">({{ __("tenant's language") }})</span></dd>
                        </dl>

                        <label class="form-label" for="special_conditions">{{ __('Special conditions') }}</label>
                        <textarea name="special_conditions" id="special_conditions" rows="7" class="form-control" x-model="conditions"
                                  placeholder="{{ __("e.g. Security deposit of two months' rent, refundable at the end of the lease.") }}"></textarea>
                        <div class="form-text mb-3">{{ __('Optional. Everything else is generated from the lease and property.') }}</div>

                        @if($contract->isSent())
                            <div class="alert alert-warning small py-2">{{ __('Sent :time. Saving sends the updated version to the tenant again.', ['time' => $contract->sent_at->diffForHumans()]) }}</div>
                            <button name="send" value="1" class="btn btn-dark w-100"><i class="bi bi-send me-1"></i> {{ __('Update & resend') }}</button>
                        @else
                            <button name="send" value="1" class="btn btn-dark w-100"><i class="bi bi-send me-1"></i> {{ __('Send to tenant for signature') }}</button>
                            <button class="btn btn-outline-dark w-100 mt-2">{{ __('Save draft') }}</button>
                        @endif
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-8 order-lg-1">
            <div class="text-muted small mb-2"><i class="bi bi-eye me-1"></i> {{ __('Preview') }}</div>
            <div class="card paper" data-bs-theme="light">
                <div class="contract-doc">{!! $preview !!}</div>
            </div>
        </div>
    </div>
@endsection
