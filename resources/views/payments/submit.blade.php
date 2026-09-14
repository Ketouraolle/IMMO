@extends('layouts.app')
@section('title', __('Pay rent'))
@section('content')
    @php $suggested = (int) round($lease->suggestedPayment()); @endphp

    <div class="page-head">
        <div>
            <h3>{{ __('Pay rent') }}</h3>
            <p class="page-head__sub">{{ $lease->property->name }} · {{ number_format($lease->rent_amount) }} XAF / {{ __($lease->billing_cycle) }}</p>
        </div>
        <a href="{{ route('payments.index') }}" class="btn btn-outline-dark btn-sm"><i class="bi bi-receipt"></i> {{ __('Payment history') }}</a>
    </div>

    <div class="row g-4">
        <div class="col-lg-7 col-xl-6">
            @include('payments._rent-status', ['lease' => $lease])

            <div class="card">
                <div class="card-body p-4">
                    <form method="POST" action="{{ route('payments.submit') }}">
                        @csrf
                        <div class="row g-3 mb-3">
                            <div class="col-sm-6">
                                <label class="form-label" for="amount">{{ __('Amount (XAF)') }}</label>
                                <input type="number" id="amount" name="amount" min="1" step="1" value="{{ old('amount', $suggested) }}" class="form-control" required>
                            </div>
                            <div class="col-sm-6">
                                <label class="form-label" for="period_covered">{{ __('Period covered') }}</label>
                                <input type="text" id="period_covered" name="period_covered" value="{{ old('period_covered', $lease->suggestedPeriodLabel()) }}" class="form-control" placeholder="{{ __('e.g. September 2026') }}">
                            </div>
                        </div>
                        <x-mobile-money-pay :phone="auth()->user()->phone" amount-field="amount" :amount="old('amount', $suggested)" :label="__('Pay')" />
                    </form>
                </div>
            </div>
        </div>
        <div class="col-lg-5 col-xl-4">
            <div class="card">
                <div class="card-body p-4">
                    <h6 class="mb-3">{{ __('How it works') }}</h6>
                    <ol class="small text-muted ps-3 mb-0">
                        <li class="mb-2">{{ __('Choose Orange Money or MTN MoMo and enter your number.') }}</li>
                        <li class="mb-2">{{ __('Approve the payment prompt on your phone.') }}</li>
                        <li>{{ __('Your receipt is issued as soon as the operator confirms.') }}</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
@endsection
