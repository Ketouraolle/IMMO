<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

// Accounts that must use two-factor authentication (admins) can only reach the Security page until it's on.
class EnsureTwoFactorIsSetUp
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && $user->mustUseTwoFactor() && ! $user->hasTwoFactorEnabled()) {
            return $request->expectsJson()
                ? abort(403, __('Two-factor authentication is required for admin accounts.'))
                : redirect()->route('security.show');
        }

        return $next($request);
    }
}
