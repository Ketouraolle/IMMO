@extends('layouts.app')
@section('title', 'Submit a payment')
@section('content')
    <h3 class="mb-1">Submit a payment</h3>
    <p class="text-muted mb-4">{{ $lease->property->name }} · {{ number_format($lease->rent_amount) }} XAF / {{ $lease->billing_cycle }}</p>

    <div class="alert alert-info">
        This records a payment you've already made (e.g. via Mobile Money or bank transfer).
        An admin will review it and generate your official receipt once validated.
    </div>

    <div class="card" style="max-width:520px;">
        <div class="card-body">
            <form method="POST" action="{{ route('payments.submit') }}">
                @csrf
                <div class="row">
                    <div class="col-6 mb-3">
                        <label class="form-label">Amount paid (XAF)</label>
                        <input type="number" name="amount" value="{{ old('amount', $lease->rent_amount) }}" class="form-control" required>
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
                <div class="mb-3">
                    <label class="form-label">Method used</label>
                    <select name="method" class="form-select" required>
                        <option value="mobile_money" {{ old('method')=='mobile_money'?'selected':'' }}>Mobile Money</option>
                        <option value="bank_transfer" {{ old('method')=='bank_transfer'?'selected':'' }}>Bank transfer</option>
                        <option value="cash" {{ old('method')=='cash'?'selected':'' }}>Cash</option>
                        <option value="other" {{ old('method')=='other'?'selected':'' }}>Other</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Notes (transaction reference, etc.)</label>
                    <textarea name="notes" class="form-control" rows="2">{{ old('notes') }}</textarea>
                </div>
                <button class="btn btn-dark">Submit for validation</button>
                <a href="{{ route('dashboard') }}" class="btn btn-link">Cancel</a>
            </form>
        </div>
    </div>
@endsection
