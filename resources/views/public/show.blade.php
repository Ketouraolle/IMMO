@extends('layouts.public')
@section('title', $property->name)
@section('content')
    <a href="{{ route('public.properties.index') }}" class="back-pill">&larr; Back to listings</a>

    <div class="row g-4">
        <div class="col-md-7">
            @include('partials.gallery', ['images' => $property->images, 'alt' => $property->name, 'emptyText' => ucfirst($property->type).' photo coming soon'])
            <h3 class="mb-1">{{ $property->name }}</h3>
            <div class="text-muted mb-3">{{ $property->address }}@if($property->city), {{ $property->city }}@endif</div>

            <div class="passport mb-3">
                <div class="passport__row">
                    <span class="passport__label">Type</span>
                    <span class="passport__value">{{ ucfirst($property->type) }}</span>
                </div>
                <div class="passport__row">
                    <span class="passport__label">Monthly rent</span>
                    <span class="passport__value">{{ number_format($property->monthly_rent) }} XAF</span>
                </div>
                <div class="passport__row">
                    <span class="passport__label">Status</span>
                    <span class="status-pill {{ $property->status == 'vacant' ? 'status-pill--vacant' : 'status-pill--occupied' }}">{{ ucfirst($property->status) }}</span>
                </div>
            </div>

            @if($property->notes)
                <p>{{ $property->notes }}</p>
            @endif

            <div class="card mt-3">
                <div class="card-body">
                    <div class="fw-semibold mb-2">Contact the agency directly</div>
                    <div class="d-flex gap-2 flex-wrap">
                        <a href="tel:+237600000000" class="btn btn-outline-dark btn-sm">Call +237 6 00 00 00 00</a>
                        <a href="mailto:contact@diasporaimmo.test" class="btn btn-outline-dark btn-sm">Email contact@diasporaimmo.test</a>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-5">
            <div class="card">
                <div class="card-header">Request a visit</div>
                <div class="card-body">
                    <form method="POST" action="{{ route('public.visit-requests.store', $property) }}" onsubmit="const b=this.querySelector('button[type=submit]'); b.disabled=true; b.textContent='Sending request…';">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">Full name</label>
                            <input type="text" name="name" value="{{ old('name') }}" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" value="{{ old('email') }}" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Phone</label>
                            <input type="text" name="phone" value="{{ old('phone') }}" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Message (optional)</label>
                            <textarea name="message" class="form-control" rows="3">{{ old('message') }}</textarea>
                        </div>
                        <button type="submit" class="btn btn-accent w-100">Request a visit</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
