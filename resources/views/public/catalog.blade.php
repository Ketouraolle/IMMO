@extends('layouts.public')
@section('title', 'Available properties — EstateHub')
@section('content')
    <section class="pub-hero">
        <div class="row g-4 align-items-stretch">
            <div class="col-lg-7">
                <div class="pub-eyebrow">Agency-managed rentals · Cameroon</div>
                <h1>Find your next home. <em>We'll show you around.</em></h1>
                <p class="lead">Every listing here is managed on the ground by EstateHub — real photos, real visits, real answers, whether the owner lives next door or an ocean away.</p>

                <div class="pub-stats">
                    <div>
                        <span class="pub-stats__num">{{ $stats['vacant'] }}</span>
                        <span class="pub-stats__label">Vacant homes ready now</span>
                    </div>
                    <div>
                        <span class="pub-stats__num">{{ $stats['cities'] }}</span>
                        <span class="pub-stats__label">Cities covered</span>
                    </div>
                    <div>
                        <span class="pub-stats__num">24h</span>
                        <span class="pub-stats__label">Typical visit-request reply</span>
                    </div>
                </div>
            </div>
            <div class="col-lg-5">
                <div class="pub-hero__aside">
                    <div class="pub-eyebrow">Why book through us</div>
                    <p class="mt-2 mb-0">Renting from a distance shouldn't mean renting blind.</p>
                    <ul>
                        <li>📸 Photos kept current — no stale listings</li>
                        <li>🗝️ A local team handles every visit request</li>
                        <li>🧾 Rent and receipts tracked in one place</li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    <livewire:property-catalog />
@endsection
