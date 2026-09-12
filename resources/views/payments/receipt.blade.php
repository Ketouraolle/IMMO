@extends('layouts.app')
@section('title', 'Receipt ' . $payment->receipt_number)
@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3 class="mb-0">Receipt</h3>
        <button onclick="window.print()" class="btn btn-outline-dark btn-sm d-print-none">Print</button>
    </div>
    <div class="card mx-auto" style="max-width:560px;">
        <div class="card-body p-4">
            <div class="d-flex justify-content-between mb-4">
                <div>
                    <div class="fw-bold fs-5">EstateHub</div>
                    <div class="text-muted small">Rent payment receipt</div>
                </div>
                <div class="text-end">
                    <div class="fw-semibold">{{ $payment->receipt_number }}</div>
                    <div class="text-muted small">{{ $payment->paid_on->format('d M Y') }}</div>
                </div>
            </div>
            <hr>
            <table class="table table-borderless mb-0">
                <tr><td class="text-muted">Tenant</td><td class="text-end">{{ $payment->lease->tenant->name }}</td></tr>
                <tr><td class="text-muted">Property</td><td class="text-end">{{ $payment->lease->property->name }}</td></tr>
                <tr><td class="text-muted">Period covered</td><td class="text-end">{{ $payment->period_covered ?? '—' }}</td></tr>
                <tr><td class="text-muted">Payment method</td><td class="text-end">{{ ucfirst(str_replace('_',' ',$payment->method)) }}</td></tr>
                <tr><td class="text-muted">Recorded by</td><td class="text-end">{{ $payment->recordedBy->name }}</td></tr>
                <tr class="border-top"><td class="fw-bold pt-3">Amount paid</td><td class="text-end fw-bold pt-3 fs-5">{{ number_format($payment->amount) }} XAF</td></tr>
            </table>
            @if($payment->notes)
                <hr><div class="text-muted small">{{ $payment->notes }}</div>
            @endif
        </div>
    </div>
@endsection
