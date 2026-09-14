<?php

namespace App\Http\Controllers;

use App\Services\TwoFactorAuthenticator;
use Illuminate\Http\Request;

// Account security: set up, confirm, and manage two-factor authentication.
class SecurityController extends Controller
{
    public function show(Request $request, TwoFactorAuthenticator $twoFactor)
    {
        $user = $request->user();
        $pending = $user->two_factor_secret !== null && $user->two_factor_confirmed_at === null;

        return view('security.show', [
            'user' => $user,
            'pending' => $pending,
            'qrCode' => $pending ? $twoFactor->qrCodeSvg($user) : null,
            'setupKey' => $pending ? trim(chunk_split($user->two_factor_secret, 4, ' ')) : null,
            'recoveryCodes' => $request->session()->get('recovery_codes'), // flashed once after confirm/regenerate
            'remainingCodes' => count($user->two_factor_recovery_codes ?? []),
        ]);
    }

    // Step 1: create a secret the user scans; it only takes effect once a code is confirmed
    public function enable(Request $request, TwoFactorAuthenticator $twoFactor)
    {
        $user = $request->user();

        if (! $user->hasTwoFactorEnabled()) {
            $user->forceFill([
                'two_factor_secret' => $twoFactor->generateSecret(),
                'two_factor_recovery_codes' => null,
                'two_factor_confirmed_at' => null,
                'two_factor_last_used_step' => null,
            ])->save();
        }

        return redirect()->route('security.show');
    }

    // Step 2: prove the app is set up correctly, then issue recovery codes
    public function confirm(Request $request, TwoFactorAuthenticator $twoFactor)
    {
        $user = $request->user();

        if ($user->two_factor_secret === null || $user->two_factor_confirmed_at !== null) {
            return redirect()->route('security.show');
        }

        $request->validate(['code' => ['required', 'string', 'max:20']]);

        if (! $twoFactor->verifyCode($user, $request->input('code'))) {
            return back()->withErrors(['code' => __('That code is not valid. Check your authenticator app and try again.')]);
        }

        $codes = $twoFactor->generateRecoveryCodes();

        $user->forceFill([
            'two_factor_confirmed_at' => now(),
            'two_factor_recovery_codes' => $twoFactor->hashRecoveryCodes($codes),
        ])->save();

        return redirect()->route('security.show')
            ->with('recovery_codes', $codes)
            ->with('status', __('Two-factor authentication is on.'));
    }

    // Turn off (password required), or cancel a setup that was never confirmed
    public function disable(Request $request)
    {
        $user = $request->user();

        if ($user->hasTwoFactorEnabled()) {
            abort_if($user->mustUseTwoFactor(), 403, __('Two-factor authentication is required for admin accounts.'));
            $request->validate(['password' => ['required', 'current_password']]);
        }

        $user->clearTwoFactor();

        return redirect()->route('security.show')->with('status', __('Two-factor authentication is off.'));
    }

    public function regenerateRecoveryCodes(Request $request, TwoFactorAuthenticator $twoFactor)
    {
        $user = $request->user();
        abort_unless($user->hasTwoFactorEnabled(), 400);

        $request->validate(['password' => ['required', 'current_password']]);

        $codes = $twoFactor->generateRecoveryCodes();
        $user->forceFill(['two_factor_recovery_codes' => $twoFactor->hashRecoveryCodes($codes)])->save();

        return redirect()->route('security.show')
            ->with('recovery_codes', $codes)
            ->with('status', __('New recovery codes generated. The old ones no longer work.'));
    }
}
