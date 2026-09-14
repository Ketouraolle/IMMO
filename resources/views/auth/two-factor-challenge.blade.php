@extends('layouts.guest')
@section('title', __('Two-factor authentication'))
@section('content')
    <div class="text-center mb-4">
        <span class="stat-icon mb-2" style="width:3rem;height:3rem;font-size:1.3rem;"><i class="bi bi-shield-lock"></i></span>
        <h1 class="h5 mb-1">{{ __('Two-factor authentication') }}</h1>
        <p class="text-muted small mb-0">{{ __('Enter the 6-digit code from your authenticator app.') }}</p>
    </div>

    <form method="POST" action="{{ route('two-factor.challenge') }}" data-busy="{{ __('Verifying…') }}"
          onsubmit="const b=this.querySelector('button[type=submit]'); b.disabled=true; b.textContent=this.dataset.busy;">
        @csrf
        <label class="form-label visually-hidden" for="code">{{ __('Authentication code') }}</label>
        <input id="code" name="code" type="text" inputmode="numeric" autocomplete="one-time-code" maxlength="7" pattern="[0-9 ]*"
               class="form-control otp-input text-center @error('code') is-invalid @enderror" placeholder="123 456" required autofocus>
        @error('code') <div class="invalid-feedback text-center">{{ $message }}</div> @enderror
        <button type="submit" class="btn btn-accent w-100 mt-3">{{ __('Verify') }}</button>
    </form>

    <details class="mt-3 small" @if($errors->has('recovery_code')) open @endif>
        <summary class="text-center text-muted" style="cursor:pointer;">{{ __('Use a recovery code instead') }}</summary>
        <form method="POST" action="{{ route('two-factor.challenge') }}" class="mt-3">
            @csrf
            <p class="text-muted">{{ __('Enter one of the recovery codes you saved when you set up two-factor authentication.') }}</p>
            <label class="form-label" for="recovery_code">{{ __('Recovery code') }}</label>
            <input id="recovery_code" name="recovery_code" type="text" autocomplete="off" spellcheck="false"
                   class="form-control text-center font-monospace @error('recovery_code') is-invalid @enderror" placeholder="abcde-12345" required>
            @error('recovery_code') <div class="invalid-feedback text-center">{{ $message }}</div> @enderror
            <button type="submit" class="btn btn-outline-dark w-100 mt-3">{{ __('Verify recovery code') }}</button>
        </form>
    </details>
@endsection
