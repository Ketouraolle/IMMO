<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'EstateHub') · EstateHub</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    @include('partials.ui-styles')
    @livewireStyles
</head>
<body>
@php
    $user = auth()->user();
    $nav = match (true) {
        $user->isAdmin() => [
            'Overview' => [
                ['Dashboard', 'bi-grid-1x2', 'dashboard', ['dashboard'], null],
                ['Properties', 'bi-buildings', 'properties.index', ['properties.*', 'leases.*', 'contracts.*'], null],
                ['Visit requests', 'bi-calendar2-week', 'visit-requests.index', ['visit-requests.*'], $navCounts['visits'] ?? 0],
            ],
            'Finance' => [
                ['Payments', 'bi-wallet2', 'payments.index', ['payments.*'], $navCounts['payments'] ?? 0],
            ],
            'Operations' => [
                ['Issues', 'bi-tools', 'issues.index', ['issues.*'], null],
                ['Users', 'bi-people', 'users.index', ['users.*'], null],
            ],
        ],
        $user->isOwner() => [
            'Overview' => [
                ['Dashboard', 'bi-grid-1x2', 'dashboard', ['dashboard'], null],
                ['My properties', 'bi-buildings', 'properties.index', ['properties.*', 'contracts.*'], null],
                ['Payments', 'bi-wallet2', 'payments.index', ['payments.*'], null],
                ['Issues', 'bi-tools', 'issues.index', ['issues.*'], null],
            ],
        ],
        default => [
            'My home' => [
                ['Dashboard', 'bi-grid-1x2', 'dashboard', ['dashboard'], null],
                ['My contract', 'bi-file-earmark-text', 'contracts.mine', ['contracts.*'], $navCounts['contract'] ?? 0],
            ],
            'Payments' => [
                ['Pay rent', 'bi-phone', 'payments.submit-form', ['payments.submit-form'], null],
                ['Payment history', 'bi-receipt', 'payments.index', ['payments.index', 'payments.receipt'], null],
            ],
            'Support' => [
                ['Issues', 'bi-tools', 'issues.index', ['issues.*'], null],
            ],
        ],
    };
    $initials = collect(explode(' ', $user->name))->filter()->take(2)->map(fn ($w) => mb_substr($w, 0, 1))->implode('');
@endphp

<aside class="offcanvas-lg offcanvas-start app-sidebar" tabindex="-1" id="appSidebar" aria-label="Main navigation">
    <div class="app-sidebar__inner">
        <div class="d-flex align-items-center justify-content-between pe-3">
            <a class="app-brand" href="{{ route('dashboard') }}"><span class="app-brand__mark"><i class="bi bi-house-door-fill"></i></span> EstateHub</a>
            <button type="button" class="btn-close btn-close-white d-lg-none" data-bs-dismiss="offcanvas" data-bs-target="#appSidebar" aria-label="Close"></button>
        </div>

        <nav class="app-nav">
            @foreach($nav as $group => $links)
                <div class="app-nav__label">{{ $group }}</div>
                @foreach($links as [$label, $icon, $route, $patterns, $badge])
                    <a href="{{ route($route) }}" class="app-nav__link {{ request()->routeIs(...$patterns) ? 'active' : '' }}">
                        <i class="bi {{ $icon }}"></i> {{ $label }}
                        @if($badge)<span class="app-nav__badge">{{ $badge }}</span>@endif
                    </a>
                @endforeach
            @endforeach
        </nav>

        <div class="app-user">
            <span class="app-user__avatar">{{ strtoupper($initials) }}</span>
            <div class="min-w-0">
                <div class="app-user__name">{{ $user->name }}</div>
                <div class="app-user__role">{{ ucfirst($user->role) }}</div>
            </div>
            <form method="POST" action="{{ route('logout') }}" class="ms-auto">
                @csrf
                <button class="app-user__logout" title="Log out" aria-label="Log out"><i class="bi bi-box-arrow-right"></i></button>
            </form>
        </div>
    </div>
</aside>

<div class="app-main">
    <header class="app-topbar">
        <button class="icon-btn d-lg-none" type="button" data-bs-toggle="offcanvas" data-bs-target="#appSidebar" aria-controls="appSidebar" aria-label="Open menu">
            <i class="bi bi-list"></i>
        </button>
        <span class="app-topbar__date d-none d-sm-inline">{{ now()->format('l, d F Y') }}</span>
        <div class="ms-auto">
            <livewire:notification-bell />
        </div>
    </header>

    <main class="app-content">
        @if($errors->any())
            <div class="alert alert-danger alert-dismissible fade show">
                <ul class="mb-0 ps-3">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @yield('content')
    </main>
</div>

<div class="toast-stack" id="toast-stack" aria-live="polite"></div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
@include('partials.ui-scripts')
@if(session('status'))
    <script>document.addEventListener('DOMContentLoaded', () => window.showToast(@js(session('status'))));</script>
@endif
@livewireScripts
</body>
</html>
