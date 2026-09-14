@extends('layouts.app')
@section('title', 'Assign tenant')
@section('content')
    <div class="page-head">
        <div>
            <a href="{{ route('properties.show', $property) }}" class="small text-muted text-decoration-none"><i class="bi bi-arrow-left"></i> {{ $property->name }}</a>
            <h3 class="mt-1">Assign a tenant</h3>
            <p class="page-head__sub">Next, you'll review the generated contract and send it for signature.</p>
        </div>
    </div>
    <div class="card" style="max-width:560px;">
        <div class="card-body p-4">
            <form method="POST" action="{{ route('leases.store', $property) }}">
                @csrf
                <div class="mb-3">
                    <label class="form-label">Tenant</label>
                    <select name="tenant_id" class="form-select" required>
                        <option value="">Select a tenant…</option>
                        @foreach($tenants as $t)
                            <option value="{{ $t->id }}" {{ old('tenant_id')==$t->id?'selected':'' }}>{{ $t->name }} ({{ $t->email }})</option>
                        @endforeach
                    </select>
                    <div class="form-text">Don't see them? <a href="{{ route('users.create') }}">Create a tenant account first</a>.</div>
                </div>
                <div class="row">
                    <div class="col-6 mb-3">
                        <label class="form-label">Start date</label>
                        <input type="date" name="start_date" value="{{ old('start_date', now()->format('Y-m-d')) }}" class="form-control" required>
                    </div>
                    <div class="col-6 mb-3">
                        <label class="form-label">End date (optional)</label>
                        <input type="date" name="end_date" value="{{ old('end_date') }}" class="form-control">
                    </div>
                </div>
                <div class="row">
                    <div class="col-6 mb-3">
                        <label class="form-label">Rent amount (XAF)</label>
                        <input type="number" name="rent_amount" value="{{ old('rent_amount', (int) $property->monthly_rent) }}" class="form-control" required>
                    </div>
                    <div class="col-6 mb-3">
                        <label class="form-label">Billing cycle</label>
                        <select name="billing_cycle" class="form-select" required>
                            @foreach(['monthly','quarterly','yearly'] as $c)
                                <option value="{{ $c }}" {{ old('billing_cycle')==$c?'selected':'' }}>{{ ucfirst($c) }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <button class="btn btn-dark">Assign & prepare contract</button>
                <a href="{{ route('properties.show', $property) }}" class="btn btn-link">Cancel</a>
            </form>
        </div>
    </div>
@endsection
