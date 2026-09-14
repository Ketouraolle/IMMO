@extends('layouts.app')
@section('title', __('Record a payment'))
@section('content')
    <div class="page-head">
        <div>
            <a href="{{ route('properties.show', $lease->property) }}" class="back-to"><i class="bi bi-arrow-left"></i> {{ $lease->property->name }}</a>
            <h3 class="mt-1">{{ __('Record a payment') }}</h3>
            <p class="page-head__sub">{{ __(':tenant · money already received by the office', ['tenant' => $lease->tenant->name]) }}</p>
        </div>
    </div>
    <div class="card" style="max-width:560px;">
        <div class="card-body p-4">
            <form method="POST" action="{{ route('payments.store', $lease) }}">
                @csrf
                <div class="row">
                    <div class="col-6 mb-3">
                        <label class="form-label">{{ __('Amount (XAF)') }}</label>
                        <input type="number" name="amount" value="{{ old('amount', (int) $lease->rent_amount) }}" class="form-control" required>
                    </div>
                    <div class="col-6 mb-3">
                        <label class="form-label">{{ __('Date paid') }}</label>
                        <input type="date" name="paid_on" value="{{ old('paid_on', now()->format('Y-m-d')) }}" class="form-control" required>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">{{ __('Period covered') }}</label>
                    <input type="text" name="period_covered" value="{{ old('period_covered', ucfirst(now()->translatedFormat('F Y'))) }}" class="form-control" placeholder="{{ __('e.g. August 2026') }}">
                </div>
                <div class="row">
                    <div class="col-6 mb-3">
                        <label class="form-label">{{ __('Method') }}</label>
                        <select name="method" class="form-select" required>
                            @foreach(\Illuminate\Support\Arr::except(\App\Models\Payment::METHODS, 'mobile_money') as $value => $label)
                                <option value="{{ $value }}" @selected(old('method', 'cash') === $value)>{{ __($label) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-6 mb-3">
                        <label class="form-label">{{ __('Reference (optional)') }}</label>
                        <input type="text" name="transaction_ref" value="{{ old('transaction_ref') }}" class="form-control" placeholder="{{ __('Transaction ID') }}">
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">{{ __('Notes') }}</label>
                    <textarea name="notes" class="form-control" rows="2">{{ old('notes') }}</textarea>
                </div>
                <div class="form-text mb-3">{{ __('Commission of :rate% is applied automatically.', ['rate' => rtrim(rtrim(number_format($lease->property->commission_rate, 2), '0'), '.')]) }}</div>
                <button class="btn btn-dark">{{ __('Record payment & issue receipt') }}</button>
                <a href="{{ route('properties.show', $lease->property) }}" class="btn btn-link">{{ __('Cancel') }}</a>
            </form>
        </div>
    </div>
@endsection
