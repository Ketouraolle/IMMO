<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'EstateHub')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    @include('partials.ui-styles')
    @livewireStyles
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark app-navbar mb-4">
    <div class="container">
        <a class="navbar-brand" href="{{ route('dashboard') }}">🏠 EstateHub</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#nav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="nav">
            @auth
            <ul class="navbar-nav me-auto">
                <li class="nav-item"><a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">Dashboard</a></li>
                <li class="nav-item"><a class="nav-link {{ request()->routeIs('properties.*') ? 'active' : '' }}" href="{{ route('properties.index') }}">Properties</a></li>
                <li class="nav-item"><a class="nav-link {{ request()->routeIs('payments.*') ? 'active' : '' }}" href="{{ route('payments.index') }}">Payments</a></li>
                <li class="nav-item"><a class="nav-link {{ request()->routeIs('issues.*') ? 'active' : '' }}" href="{{ route('issues.index') }}">Issues</a></li>
                @if(auth()->user()->isAdmin())
                <li class="nav-item"><a class="nav-link {{ request()->routeIs('users.*') ? 'active' : '' }}" href="{{ route('users.index') }}">Users</a></li>
                @endif
            </ul>
            <span class="navbar-text text-light me-3">
                {{ auth()->user()->name }}
                <span class="badge badge-role-{{ auth()->user()->role }}">{{ ucfirst(auth()->user()->role) }}</span>
            </span>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button class="btn btn-outline-light btn-sm">Log out</button>
            </form>
            @endauth
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
    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @yield('content')
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
@livewireScripts
</body>
</html>
