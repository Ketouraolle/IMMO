@extends('layouts.app')
@section('title', __('Security'))
@section('content')
    @php
        $enabled = $user->hasTwoFactorEnabled();
        $passwordAction = $errors->has('password') ? (old('_method') === 'DELETE' ? 'disable' : 'regenerate') : null;
    @endphp

    <div class="page-head">
        <div>
            <h3>{{ __('Security') }}</h3>
            <p class="page-head__sub">{{ __('Protect your account with a second step at sign-in.') }}</p>
        </div>
    </div>

    @if($user->mustUseTwoFactor() && ! $enabled)
        <div class="alert alert-warning d-flex gap-2 align-items-start">
            <i class="bi bi-shield-exclamation fs-5"></i>
            <div><strong>{{ __('Two-factor authentication is required for admin accounts.') }}</strong> {{ __('Set it up now to continue using EstateHub.') }}</div>
        </div>
    @endif

    <div class="row g-4">
        <div class="col-lg-7">
            <div class="card">
                <div class="card-body p-4">
                    <div class="d-flex gap-3 align-items-start">
                        <span class="stat-icon"><i class="bi bi-shield-lock"></i></span>
                        <div class="flex-grow-1">
                            <div class="d-flex flex-wrap gap-2 align-items-center">
                                <h5 class="mb-0">{{ __('Two-factor authentication') }}</h5>
                                @if($enabled)
                                    <span class="badge-soft badge-soft--success">{{ __('On') }}</span>
                                @elseif($pending)
                                    <span class="badge-soft badge-soft--warning">{{ __('Setup in progress') }}</span>
                                @else
                                    <span class="badge-soft badge-soft--neutral">{{ __('Off') }}</span>
                                @endif
                            </div>
                            <p class="text-muted small mb-0 mt-1">{{ __('After your password, you will enter a 6-digit code from an authenticator app such as Google Authenticator, Microsoft Authenticator or Authy.') }}</p>
                        </div>
                    </div>

                    @if($pending)
                        <hr class="my-4">
                        <div class="step-label"><span class="step-label__num">1</span> {{ __('Scan this QR code with your authenticator app') }}</div>
                        <div class="d-flex flex-wrap gap-4 align-items-center mb-4">
                            <div class="qr-box" role="img" aria-label="{{ __('QR code for your authenticator app') }}">{!! $qrCode !!}</div>
                            <div class="small min-w-0">
                                <div class="text-muted mb-1">{{ __("Can't scan? Enter this key manually:") }}</div>
                                <code class="setup-key">{{ $setupKey }}</code>
                            </div>
                        </div>

                        <div class="step-label"><span class="step-label__num">2</span> {{ __('Enter the 6-digit code it shows') }}</div>
                        <form method="POST" action="{{ route('security.two-factor.confirm') }}" class="d-flex gap-2 flex-wrap align-items-start">
                            @csrf
                            <div>
                                <input name="code" type="text" inputmode="numeric" autocomplete="one-time-code" maxlength="7" pattern="[0-9 ]*"
                                       class="form-control otp-input @error('code') is-invalid @enderror" style="max-width: 11rem;" placeholder="123 456"
                                       aria-label="{{ __('Authentication code') }}" required autofocus>
                                @error('code') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <button class="btn btn-dark py-2">{{ __('Confirm and turn on') }}</button>
                        </form>
                        <form method="POST" action="{{ route('security.two-factor.disable') }}" class="mt-2">
                            @csrf @method('DELETE')
                            <button class="btn btn-link btn-sm px-0 text-muted">{{ __('Cancel setup') }}</button>
                        </form>

                    @elseif($enabled)
                        <hr class="my-4">
                        <p class="small mb-3">
                            {{ __('Turned on :date.', ['date' => $user->two_factor_confirmed_at->translatedFormat('j F Y')]) }}
                            {{ trans_choice(':count recovery code left.|:count recovery codes left.', $remainingCodes) }}
                        </p>

                        @if($recoveryCodes)
                            <div class="recovery-box mb-4">
                                <div class="fw-semibold mb-1"><i class="bi bi-key"></i> {{ __('Save your recovery codes') }}</div>
                                <p class="small text-muted">{{ __('Each code works once if you lose access to your authenticator app. They will not be shown again.') }}</p>
                                <div class="recovery-codes">
                                    @foreach($recoveryCodes as $code)
                                        <code>{{ $code }}</code>
                                    @endforeach
                                </div>
                                <button type="button" class="btn btn-outline-dark btn-sm mt-3" x-data="{ copied: false }"
                                        @click="navigator.clipboard?.writeText(@js(implode("\n", $recoveryCodes))); copied = true; setTimeout(() => copied = false, 2000)">
                                    <i class="bi" :class="copied ? 'bi-check2' : 'bi-clipboard'"></i>
                                    <span x-text="copied ? @js(__('Copied')) : @js(__('Copy codes'))">{{ __('Copy codes') }}</span>
                                </button>
                            </div>
                        @endif

                        <div x-data="{ action: @js($passwordAction) }">
                            <div class="d-flex flex-wrap gap-2">
                                <button type="button" class="btn btn-outline-dark btn-sm" @click="action = action === 'regenerate' ? null : 'regenerate'">
                                    <i class="bi bi-arrow-repeat"></i> {{ __('New recovery codes') }}
                                </button>
                                @unless($user->mustUseTwoFactor())
                                    <button type="button" class="btn btn-outline-danger btn-sm" @click="action = action === 'disable' ? null : 'disable'">{{ __('Turn off') }}</button>
                                @endunless
                            </div>
                            {{-- x-show sits on a wrapper: Bootstrap's d-flex uses !important and would override it --}}
                            <div x-show="action" x-cloak>
                                <form method="POST" class="d-flex gap-2 flex-wrap mt-3"
                                      :action="action === 'disable' ? @js(route('security.two-factor.disable')) : @js(route('security.two-factor.recovery-codes'))">
                                    @csrf
                                    <input type="hidden" name="_method" :value="action === 'disable' ? 'DELETE' : 'POST'">
                                    <input type="password" name="password" autocomplete="current-password" required
                                           class="form-control form-control-sm @error('password') is-invalid @enderror" style="max-width: 16rem;"
                                           placeholder="{{ __('Confirm with your password') }}" aria-label="{{ __('Password') }}">
                                    <button class="btn btn-sm" :class="action === 'disable' ? 'btn-danger' : 'btn-dark'"
                                            x-text="action === 'disable' ? @js(__('Turn off')) : @js(__('Generate'))"></button>
                                </form>
                            </div>
                            @error('password') <div class="text-danger small mt-2">{{ $message }}</div> @enderror
                        </div>

                        @if($user->mustUseTwoFactor())
                            <p class="text-muted small mt-3 mb-0"><i class="bi bi-info-circle"></i> {{ __('Two-factor authentication is required for admin accounts.') }}</p>
                        @endif

                    @else
                        <form method="POST" action="{{ route('security.two-factor.enable') }}" class="mt-4">
                            @csrf
                            <button class="btn btn-dark"><i class="bi bi-shield-plus me-1"></i> {{ __('Set up two-factor authentication') }}</button>
                        </form>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="card">
                <div class="card-body p-4">
                    <h6 class="mb-3">{{ __('Good to know') }}</h6>
                    <ul class="small text-muted ps-3 mb-0">
                        <li class="mb-2">{{ __('Any authenticator app works: Google Authenticator, Microsoft Authenticator, Authy or 1Password.') }}</li>
                        <li class="mb-2">{{ __('Keep your recovery codes somewhere safe, like a password manager.') }}</li>
                        <li>{{ __('Lost both your phone and your recovery codes? Contact the office to reset your access.') }}</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
@endsection
