@extends('layouts.app')
@section('title', 'Assign tenant')
@section('content')
    <h3 class="mb-4">Assign a tenant to {{ $property->name }}</h3>
    <div class="card" style="max-width:560px;">
        <div class="card-body">
            <form method="POST" action="{{ route('leases.store', $property) }}" enctype="multipart/form-data">
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
                        <input type="number" name="rent_amount" value="{{ old('rent_amount', $property->monthly_rent) }}" class="form-control" required>
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
                <div class="mb-3">
                    <label class="form-label">Signed lease contract (PDF, optional)</label>
                    <input type="file" name="document" accept="application/pdf" class="form-control">
                    <div class="form-text">Can be added later too — the tenant will see it once uploaded.</div>
                </div>
                <button class="btn btn-dark">Assign tenant</button>
                <a href="{{ route('properties.show', $property) }}" class="btn btn-link">Cancel</a>
            </form>
        </div>
    </div>
@endsection
