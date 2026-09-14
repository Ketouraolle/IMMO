<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'EstateHub — Find your next home')</title>
    {{-- Reveal-on-scroll only hides content once JS is known to run --}}
    <script>document.documentElement.classList.add('js-reveal');</script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    @include('partials.ui-styles')
    @include('partials.public-styles')
    @livewireStyles
</head>
<body class="@yield('body-class')">
<header class="site-header">
    <div class="container site-header__inner">
        <a class="site-brand" href="{{ route('public.properties.index') }}">
            <span class="site-brand__mark"><i class="bi bi-house-door-fill"></i></span> EstateHub
        </a>
        <a href="{{ route('login') }}" class="site-header__link">Log in</a>
    </div>
</header>

<main class="container site-main">
    @if(session('status'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('status') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @yield('content')
</main>

<footer class="site-footer">
    <div class="container site-footer__inner">
        <span>&copy; {{ date('Y') }} EstateHub · Rentals managed in Cameroon</span>
        <span class="d-flex flex-wrap gap-3">
            <a href="tel:+237600000000"><i class="bi bi-telephone me-1"></i>+237 6 00 00 00 00</a>
            <a href="mailto:contact@diasporaimmo.test"><i class="bi bi-envelope me-1"></i>contact@diasporaimmo.test</a>
        </span>
    </div>
</footer>

<div class="toast-stack" id="toast-stack" aria-live="polite"></div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
@include('partials.ui-scripts')
<script>
    (() => {
        // Header shadow once the page scrolls
        const header = document.querySelector('.site-header');
        const onScroll = () => header?.classList.toggle('is-scrolled', window.scrollY > 8);
        window.addEventListener('scroll', onScroll, { passive: true });
        onScroll();

        // Fade elements in as they enter the viewport
        const observer = 'IntersectionObserver' in window
            ? new IntersectionObserver((entries) => {
                entries.forEach((entry) => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('is-visible');
                        observer.unobserve(entry.target);
                    }
                });
            }, { rootMargin: '0px 0px -40px 0px', threshold: 0.08 })
            : null;

        const reveal = () => document.querySelectorAll('[data-reveal]:not(.is-visible)')
            .forEach((el) => observer ? observer.observe(el) : el.classList.add('is-visible'));

        reveal();
        // Livewire re-renders (filters, pagination) bring in new cards
        document.addEventListener('livewire:init', () => {
            Livewire.hook('commit', ({ succeed }) => succeed(() => requestAnimationFrame(reveal)));
        });
    })();
</script>
@livewireScripts
</body>
</html>
