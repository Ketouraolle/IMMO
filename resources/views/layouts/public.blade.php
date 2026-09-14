<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'EstateHub — Find your next home')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,500;9..144,600;9..144,700&family=IBM+Plex+Mono:wght@500;600&family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    @include('partials.ui-styles')
    @include('partials.public-styles')
    @livewireStyles
</head>
<body>
<div class="airmail-stripe"></div>
<nav class="navbar navbar-expand-lg navbar-dark pub-navbar">
    <div class="container">
        <a class="navbar-brand" href="{{ route('public.properties.index') }}"><span class="mark">🏠</span> EstateHub</a>
        <div class="ms-auto">
            <a href="{{ route('login') }}" class="btn btn-login">Staff / Owner / Tenant log in</a>
        </div>
    </div>
</nav>

<div class="container pb-5">
    @if(session('status'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('status') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @yield('content')
</div>

<footer class="pub-footer">
    <div class="container">
        <div class="row g-4">
            <div class="col-md-5">
                <h6>🏠 EstateHub</h6>
                <p>Rental properties managed on the ground in Cameroon, for owners and tenants — near or far from home.</p>
            </div>
            <div class="col-6 col-md-3">
                <h6>Browse</h6>
                <p><a href="{{ route('public.properties.index') }}">All listings</a></p>
                <p><a href="{{ route('login') }}">Staff / Owner / Tenant login</a></p>
            </div>
            <div class="col-6 col-md-4">
                <h6>Contact the agency</h6>
                <p><a href="tel:+237600000000">+237 6 00 00 00 00</a></p>
                <p><a href="mailto:contact@diasporaimmo.test">contact@diasporaimmo.test</a></p>
            </div>
        </div>
        <div class="pub-footer__bottom">&copy; {{ date('Y') }} EstateHub. All rights reserved.</div>
    </div>
</footer>
<div class="airmail-stripe"></div>

<div class="toast-stack" id="toast-stack" aria-live="polite"></div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
@include('partials.ui-scripts')
@livewireScripts
</body>
</html>
