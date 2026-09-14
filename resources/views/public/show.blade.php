@extends('layouts.public')
@section('title', $property->name.' — EstateHub')
@section('content')
    <a href="{{ route('public.properties.index') }}" class="back-link"><i class="bi bi-arrow-left"></i> {{ __('All homes') }}</a>

    <div class="row g-4 g-lg-5">
        <div class="col-lg-7">
            @include('partials.gallery', ['images' => $property->images, 'alt' => $property->name, 'emptyText' => __('Photos coming soon')])

            <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
                <div>
                    <h1 class="listing-title">{{ $property->name }}</h1>
                    <div class="listing-location"><i class="bi bi-geo-alt"></i> {{ $property->address }}@if($property->city), {{ $property->city }}@endif</div>
                </div>
                <div class="listing-price">{{ number_format($property->monthly_rent) }} XAF <span>{{ __('/ month') }}</span></div>
            </div>
            <a href="#book" class="btn btn-accent w-100 py-2 mt-3 d-lg-none">{{ __('Book a visit') }}</a>

            <div class="listing-facts">
                <div><span>{{ __('Type') }}</span><strong>{{ __(ucfirst($property->type)) }}</strong></div>
                <div><span>{{ __('Availability') }}</span><strong>{{ $property->status === 'vacant' ? __('Available now') : __(ucfirst($property->status)) }}</strong></div>
                <div><span>{{ __('Visit') }}</span><strong>{{ $property->visit_fee > 0 ? number_format($property->visit_fee).' XAF' : __('Free') }}</strong></div>
            </div>

            @if($property->notes)
                <div class="listing-about">
                    <h2 class="section-title mb-2">{{ __('About this home') }}</h2>
                    <p class="mb-0">{{ $property->notes }}</p>
                </div>
            @endif

            <div class="listing-contact">
                {!! __('Questions? :call or :email.', [
                    'call' => '<a href="tel:+237600000000">'.e(__('Call +237 6 00 00 00 00')).'</a>',
                    'email' => '<a href="mailto:contact@diasporaimmo.test">'.e(__('email the agency')).'</a>',
                ]) !!}
            </div>
        </div>

        <div class="col-lg-5" id="book" style="scroll-margin-top: 80px;">
            <div class="sticky-lg-top" style="top: 88px;">
                <livewire:visit-booking :property="$property" />
            </div>
        </div>
    </div>
@endsection
