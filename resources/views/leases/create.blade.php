@extends('layouts.app')
@section('title', __('Assign a tenant'))
@section('content')
    <div class="page-head">
        <div>
            <a href="{{ route('properties.show', $property) }}" class="back-to"><i class="bi bi-arrow-left"></i> {{ $property->name }}</a>
            <h3 class="mt-1">{{ __('Assign a tenant') }}</h3>
            <p class="page-head__sub">{{ __("Next, you'll review the generated contract and send it for signature.") }}</p>
        </div>
    </div>
    <div class="card" style="max-width:560px;">
        <div class="card-body p-4">
            <form method="POST" action="{{ route('leases.store', $property) }}">
                @csrf
                <div class="mb-3">
                    <label class="form-label">{{ __('Tenant') }}</label>
                    <select name="tenant_id" class="form-select" required>
                        <option value="">{{ __('Select a tenant…') }}</option>
                        @foreach($tenants as $t)
                            <option value="{{ $t->id }}" {{ old('tenant_id') == $t->id ? 'selected' : '' }}>{{ $t->name }} ({{ $t->email }})</option>
                        @endforeach
                    </select>
                    <div class="form-text">
                        {!! __("Don't see them? :link.", ['link' => '<a href="'.e(route('users.create')).'">'.e(__('Create a tenant account first')).'</a>']) !!}
                    </div>
                </div>
                <div class="row">
                    <div class="col-6 mb-3">
                        <label class="form-label">{{ __('Start date') }}</label>
                        <input type="date" name="start_date" value="{{ old('start_date', now()->format('Y-m-d')) }}" class="form-control" required>
                    </div>
                    <div class="col-6 mb-3">
                        <label class="form-label">{{ __('End date (optional)') }}</label>
                        <input type="date" name="end_date" value="{{ old('end_date') }}" class="form-control">
                    </div>
                </div>
                <div class="row">
                    <div class="col-6 mb-3">
                        <label class="form-label">{{ __('Rent amount (XAF)') }}</label>
                        <input type="number" name="rent_amount" value="{{ old('rent_amount', (int) $property->monthly_rent) }}" class="form-control" required>
                    </div>
                    <div class="col-6 mb-3">
                        <label class="form-label">{{ __('Billing cycle') }}</label>
                        <select name="billing_cycle" class="form-select" required>
                            @foreach(['monthly', 'quarterly', 'yearly'] as $c)
                                <option value="{{ $c }}" @selected(old('billing_cycle') === $c)>{{ __(ucfirst($c)) }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <button class="btn btn-dark">{{ __('Assign & prepare contract') }}</button>
                <a href="{{ route('properties.show', $property) }}" class="btn btn-link">{{ __('Cancel') }}</a>
            </form>
        </div>
    </div>
@endsection
