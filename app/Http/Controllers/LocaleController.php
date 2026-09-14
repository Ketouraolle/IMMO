<?php

namespace App\Http\Controllers;

use App\Support\Locale;
use Illuminate\Http\Request;

class LocaleController extends Controller
{
    // Remember the language for this visit, and on the account when signed in
    public function __invoke(Request $request, string $locale)
    {
        abort_unless(isset(Locale::SUPPORTED[$locale]), 404);

        $request->session()->put('locale', $locale);
        $request->user()?->update(['locale' => $locale]);

        return back(fallback: route('public.properties.index'));
    }
}
