@extends('layouts.public')
@section('title', $property->name.' — EstateHub')
@section('content')
    <a href="{{ route('public.properties.index') }}" class="back-link"><i class="bi bi-arrow-left"></i> All homes</a>

    <div class="row g-4 g-lg-5">
        <div class="col-lg-7">
            @include('partials.gallery', ['images' => $property->images, 'alt' => $property->name, 'emptyText' => 'Photos coming soon'])

            <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
                <div>
                    <h1 class="listing-title">{{ $property->name }}</h1>
                    <div class="listing-location"><i class="bi bi-geo-alt"></i> {{ $property->address }}@if($property->city), {{ $property->city }}@endif</div>
                </div>
                <div class="listing-price">{{ number_format($property->monthly_rent) }} XAF <span>/ month</span></div>
            </div>
            <a href="#book" class="btn btn-accent w-100 py-2 mt-3 d-lg-none">Book a visit</a>

            <div class="listing-facts">
                <div><span>Type</span><strong>{{ ucfirst($property->type) }}</strong></div>
                <div><span>Availability</span><strong>{{ $property->status === 'vacant' ? 'Available now' : ucfirst($property->status) }}</strong></div>
                <div><span>Visit</span><strong>{{ $property->visit_fee > 0 ? number_format($property->visit_fee).' XAF' : 'Free' }}</strong></div>
            </div>

            @if($property->notes)
                <div class="listing-about">
                    <h2 class="section-title mb-2">About this home</h2>
                    <p class="mb-0">{{ $property->notes }}</p>
                </div>
            @endif

            <div class="listing-contact">
                Questions? <a href="tel:+237600000000">Call +237 6 00 00 00 00</a> or <a href="mailto:contact@diasporaimmo.test">email the agency</a>.
            </div>
        </div>

        <div class="col-lg-5" id="book" style="scroll-margin-top: 80px;">
            <div class="sticky-lg-top" style="top: 88px;">
                <livewire:visit-booking :property="$property" />
            </div>
        </div>
    </div>
@endsection
