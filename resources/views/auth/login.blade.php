@extends('layouts.guest')
@section('title', 'Log in')
@section('content')
    <p class="text-center text-muted small mb-4">For staff, owners and tenants with an account.</p>
    @if($errors->any())
        <div class="alert alert-danger py-2">{{ $errors->first() }}</div>
    @endif
    <form method="POST" action="{{ route('login') }}" onsubmit="const b=this.querySelector('button[type=submit]'); b.disabled=true; b.textContent='Logging in…';">
        @csrf
        <div class="mb-3">
            <label class="form-label">Email</label>
            <input type="email" name="email" value="{{ old('email') }}" class="form-control" required autofocus>
        </div>
        <div class="mb-3">
            <label class="form-label">Password</label>
            <input type="password" name="password" class="form-control" required>
        </div>
        <div class="form-check mb-3">
            <input type="checkbox" name="remember" class="form-check-input" id="remember">
            <label class="form-check-label" for="remember">Remember me</label>
        </div>
        <button type="submit" class="btn btn-accent w-100">Log in</button>
    </form>
@endsection
