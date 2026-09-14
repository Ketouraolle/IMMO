@extends('layouts.app')
@section('title', __('Receipt :number', ['number' => $payment->receipt_number]))
@section('content')
    <div class="page-head d-print-none">
        <div><h3>{{ __('Receipt') }}</h3></div>
        <div class="d-flex gap-2">
            <a href="{{ route('payments.index') }}" class="btn btn-outline-dark btn-sm">{{ __('All payments') }}</a>
            <button onclick="window.print()" class="btn btn-dark btn-sm"><i class="bi bi-printer"></i> {{ __('Print') }}</button>
        </div>
    </div>
    <div class="card mx-auto" style="max-width:560px;">
        <div class="card-body p-4 p-sm-5">
            <div class="d-flex justify-content-between mb-4">
                <div>
                    <div class="fw-bold fs-5">EstateHub</div>
                    <div class="text-muted small">{{ __('Rent payment receipt') }}</div>
                </div>
                <div class="text-end">
                    <div class="fw-semibold">{{ $payment->receipt_number }}</div>
                    <div class="text-muted small">{{ $payment->paid_on->translatedFormat('d M Y') }}</div>
                </div>
            </div>
            <div class="text-center py-3 mb-3 rounded-3" style="background: var(--soft-success-bg);">
                <div class="small fw-semibold" style="color: var(--soft-success-fg);"><i class="bi bi-check-circle-fill"></i> {{ __('Paid') }}</div>
                <div class="fs-3 fw-bold">{{ number_format($payment->amount) }} XAF</div>
            </div>
            <table class="table table-borderless mb-0 small">
                <tr><td class="text-muted ps-0">{{ __('Tenant') }}</td><td class="text-end pe-0">{{ $payment->lease->tenant->name }}</td></tr>
                <tr><td class="text-muted ps-0">{{ __('Property') }}</td><td class="text-end pe-0">{{ $payment->lease->property->name }}</td></tr>
                <tr><td class="text-muted ps-0">{{ __('Period covered') }}</td><td class="text-end pe-0">{{ $payment->period_covered ?? '—' }}</td></tr>
                <tr><td class="text-muted ps-0">{{ __('Payment method') }}</td><td class="text-end pe-0">{{ $payment->methodLabel() }}</td></tr>
                @if($payment->transaction_ref)
                    <tr><td class="text-muted ps-0">{{ __('Transaction ref.') }}</td><td class="text-end pe-0 font-monospace">{{ $payment->transaction_ref }}</td></tr>
                @endif
                <tr><td class="text-muted ps-0">{{ $payment->recordedBy ? __('Recorded by') : __('Confirmed by') }}</td><td class="text-end pe-0">{{ $payment->recordedBy?->name ?? $payment->methodLabel() }}</td></tr>
            </table>
            @if($payment->notes)
                <hr><div class="text-muted small">{{ $payment->notes }}</div>
            @endif
        </div>
    </div>
@endsection
