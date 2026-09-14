@extends('layouts.app')
@section('title', 'Record payment')
@section('content')
    <div class="page-head">
        <div>
            <a href="{{ route('properties.show', $lease->property) }}" class="small text-muted text-decoration-none"><i class="bi bi-arrow-left"></i> {{ $lease->property->name }}</a>
            <h3 class="mt-1">Record a payment</h3>
            <p class="page-head__sub">{{ $lease->tenant->name }} · money already received by the office</p>
        </div>
    </div>
    <div class="card" style="max-width:560px;">
        <div class="card-body p-4">
            <form method="POST" action="{{ route('payments.store', $lease) }}">
                @csrf
                <div class="row">
                    <div class="col-6 mb-3">
                        <label class="form-label">Amount (XAF)</label>
                        <input type="number" name="amount" value="{{ old('amount', (int) $lease->rent_amount) }}" class="form-control" required>
                    </div>
                    <div class="col-6 mb-3">
                        <label class="form-label">Date paid</label>
                        <input type="date" name="paid_on" value="{{ old('paid_on', now()->format('Y-m-d')) }}" class="form-control" required>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Period covered</label>
                    <input type="text" name="period_covered" value="{{ old('period_covered', now()->format('F Y')) }}" class="form-control" placeholder="e.g. August 2026">
                </div>
                <div class="row">
                    <div class="col-6 mb-3">
                        <label class="form-label">Method</label>
                        <select name="method" class="form-select" required>
                            @foreach(\Illuminate\Support\Arr::except(\App\Models\Payment::METHODS, 'mobile_money') as $value => $label)
                                <option value="{{ $value }}" @selected(old('method', 'cash') === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-6 mb-3">
                        <label class="form-label">Reference (optional)</label>
                        <input type="text" name="transaction_ref" value="{{ old('transaction_ref') }}" class="form-control" placeholder="Transaction ID">
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Notes</label>
                    <textarea name="notes" class="form-control" rows="2">{{ old('notes') }}</textarea>
                </div>
                <div class="form-text mb-3">Commission of {{ rtrim(rtrim(number_format($lease->property->commission_rate, 2), '0'), '.') }}% is applied automatically.</div>
                <button class="btn btn-dark">Record payment & issue receipt</button>
                <a href="{{ route('properties.show', $lease->property) }}" class="btn btn-link">Cancel</a>
            </form>
        </div>
    </div>
@endsection
