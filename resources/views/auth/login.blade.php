@extends('layouts.guest')
@section('title', __('Log in'))
@section('content')
    <p class="text-center text-muted small mb-4">{{ __('For staff, owners and tenants with an account.') }}</p>
    @if($errors->any())
        <div class="alert alert-danger py-2">{{ $errors->first() }}</div>
    @endif
    <form method="POST" action="{{ route('login') }}" data-busy="{{ __('Logging in…') }}"
          onsubmit="const b=this.querySelector('button[type=submit]'); b.disabled=true; b.textContent=this.dataset.busy;">
        @csrf
        <div class="mb-3">
            <label class="form-label" for="email">{{ __('Email') }}</label>
            <input type="email" id="email" name="email" value="{{ old('email') }}" class="form-control" required autofocus autocomplete="username">
        </div>
        <div class="mb-3">
            <label class="form-label" for="password">{{ __('Password') }}</label>
            <input type="password" id="password" name="password" class="form-control" required autocomplete="current-password">
        </div>
        <div class="form-check mb-3">
            <input type="checkbox" name="remember" class="form-check-input" id="remember">
            <label class="form-check-label" for="remember">{{ __('Remember me') }}</label>
        </div>
        <button type="submit" class="btn btn-accent w-100">{{ __('Log in') }}</button>
    </form>
@endsection
