<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    // How long a password-verified login waits for its two-factor code
    public const CHALLENGE_MINUTES = 5;

    public function create()
    {
        return view('auth.login');
    }

    public function store(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (! Auth::validate($credentials)) {
            return back()->withErrors([
                'email' => __('Those credentials do not match our records.'),
            ])->onlyInput('email');
        }

        $user = Auth::getLastAttempted();

        if (! $user->is_active) {
            return back()->withErrors(['email' => __('This account has been deactivated. Contact the office.')])->onlyInput('email');
        }

        // Password is right; accounts with two-factor authentication still need a code before signing in
        if ($user->hasTwoFactorEnabled()) {
            $request->session()->put([
                'login.id' => $user->id,
                'login.remember' => $request->boolean('remember'),
                'login.expires_at' => now()->addMinutes(self::CHALLENGE_MINUTES)->timestamp,
            ]);

            return redirect()->route('two-factor.challenge');
        }

        Auth::login($user, $request->boolean('remember'));
        $request->session()->regenerate();

        return redirect()->intended(route('dashboard'));
    }

    public function destroy(Request $request)
    {
        // Keep the visitor's language across logout
        $locale = $request->session()->get('locale');

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        if ($locale) {
            $request->session()->put('locale', $locale);
        }

        return redirect('/login');
    }
}
