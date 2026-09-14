<?php

namespace App\Support;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\App;

class Locale
{
    public const SUPPORTED = ['en' => 'English', 'fr' => 'Français'];

    /** Account preference, then the visitor's choice this session, then the browser's language. */
    public static function resolve(Request $request): string
    {
        $candidates = [
            $request->user()?->locale,
            $request->hasSession() ? $request->session()->get('locale') : null,
        ];

        foreach ($candidates as $locale) {
            if ($locale && isset(self::SUPPORTED[$locale])) {
                return $locale;
            }
        }

        return $request->getPreferredLanguage(array_keys(self::SUPPORTED)) ?? config('app.locale');
    }

    public static function set(string $locale): void
    {
        App::setLocale($locale);
        Carbon::setLocale($locale);
    }

    /** Run a callback in another language (e.g. render a contract in the tenant's language). */
    public static function using(string $locale, callable $callback): mixed
    {
        $previous = App::getLocale();
        self::set(isset(self::SUPPORTED[$locale]) ? $locale : config('app.locale'));

        try {
            return $callback();
        } finally {
            self::set($previous);
        }
    }
}
