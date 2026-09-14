<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\TwoFactorAuthenticator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

// Second sign-in step: the password was verified by LoginController, which left the user id in the session.
class TwoFactorChallengeController extends Controller
{
    public function create(Request $request)
    {
        if (! $this->pendingUser($request)) {
            return redirect()->route('login');
        }

        return view('auth.two-factor-challenge');
    }

    public function store(Request $request, TwoFactorAuthenticator $twoFactor)
    {
        $user = $this->pendingUser($request);

        if (! $user) {
            return redirect()->route('login')->withErrors(['email' => __('Your sign-in session expired. Please log in again.')]);
        }

        $request->validate([
            'code' => ['nullable', 'string', 'max:20'],
            'recovery_code' => ['nullable', 'string', 'max:40'],
        ]);

        $usingRecoveryCode = $request->filled('recovery_code');

        $valid = $usingRecoveryCode
            ? $twoFactor->useRecoveryCode($user, $request->input('recovery_code'))
            : $request->filled('code') && $twoFactor->verifyCode($user, $request->input('code'));

        if (! $valid) {
            return back()->withErrors($usingRecoveryCode
                ? ['recovery_code' => __('That recovery code is not valid.')]
                : ['code' => __('That code is not valid. Check your authenticator app and try again.')]);
        }

        $remember = (bool) $request->session()->pull('login.remember', false);
        $request->session()->forget(['login.id', 'login.expires_at']);

        Auth::login($user, $remember);
        $request->session()->regenerate();

        $response = redirect()->intended(route('dashboard'));

        if ($usingRecoveryCode) {
            $left = count($user->two_factor_recovery_codes ?? []);
            $response->with('status', trans_choice('Recovery code used. You have :count code left.|Recovery code used. You have :count codes left.', $left));
        }

        return $response;
    }

    private function pendingUser(Request $request): ?User
    {
        $id = $request->session()->get('login.id');
        $expiresAt = $request->session()->get('login.expires_at');

        if (! $id || ! $expiresAt || now()->timestamp > $expiresAt) {
            $request->session()->forget(['login.id', 'login.remember', 'login.expires_at']);

            return null;
        }

        $user = User::find($id);

        return $user && $user->is_active && $user->hasTwoFactorEnabled() ? $user : null;
    }
}
