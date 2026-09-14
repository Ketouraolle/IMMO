@extends('layouts.public')
@section('title', 'Find your next home — EstateHub')
@section('body-class', 'page-home')
@section('content')
    <section class="hero">
        <span class="hero-pill" data-reveal>
            <span class="hero-pill__dot" aria-hidden="true"></span>
            {{ $stats['vacant'] }} {{ Str::plural('home', $stats['vacant']) }} available now
            @if($stats['cities'])<span class="text-muted fw-normal">· {{ $stats['cities'] }} {{ Str::plural('city', $stats['cities']) }}</span>@endif
        </span>
        <h1 data-reveal style="--reveal-delay: 80ms;">Find your next home, <span class="hero-accent">visit it this week.</span></h1>
        <p data-reveal style="--reveal-delay: 160ms;">Homes managed on the ground in Cameroon. Book a visit online in a minute and pay with Orange Money or MoMo.</p>
    </section>

    <livewire:property-catalog />

    <section class="steps" aria-labelledby="how-it-works">
        <div class="text-center mb-4" data-reveal>
            <div class="steps__eyebrow">How it works</div>
            <h2 id="how-it-works" class="section-title fs-4 mt-1">From browsing to moving in</h2>
        </div>
        <div class="row g-3 g-lg-4">
            @foreach([
                ['bi-search', 'Find a home', 'Filter by city, type and budget. Every listing is managed by our local team.'],
                ['bi-calendar2-check', 'Book a visit', 'Pick a day and time that suits you. We confirm by phone or email.'],
                ['bi-phone', 'Pay the easy way', 'Visit fees and rent by Orange Money or MTN MoMo, with instant receipts.'],
            ] as $i => [$icon, $title, $text])
                <div class="col-md-4" data-reveal style="--reveal-delay: {{ $i * 90 }}ms;">
                    <div class="step">
                        <span class="step__num">0{{ $i + 1 }}</span>
                        <span class="step__icon"><i class="bi {{ $icon }}"></i></span>
                        <h3>{{ $title }}</h3>
                        <p>{{ $text }}</p>
                    </div>
                </div>
            @endforeach
        </div>
    </section>
@endsection
