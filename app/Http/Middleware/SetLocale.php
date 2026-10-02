<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    public const LOCALES = ['en' => 'English', 'ru' => 'Русский', 'hy' => 'Հայերեն'];

    public function handle(Request $request, Closure $next): Response
    {
        $locale = $request->session()->get('locale')
            ?? $request->cookie('locale')
            ?? $request->getPreferredLanguage(array_keys(self::LOCALES))
            ?? config('app.locale');

        if (array_key_exists($locale, self::LOCALES)) {
            app()->setLocale($locale);
        }

        return $next($request);
    }
}
