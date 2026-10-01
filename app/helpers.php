<?php

use App\Models\Setting;

if (! function_exists('money')) {
    function money(float|string|null $amount, bool $decimals = false): string
    {
        $symbol = Setting::get('currency_symbol', '$');

        return $symbol.number_format((float) $amount, $decimals ? 2 : 0, '.', ',');
    }
}

if (! function_exists('setting')) {
    function setting(string $key, mixed $default = null): mixed
    {
        return Setting::get($key, $default);
    }
}

if (! function_exists('locale_switch_url')) {
    /** Current URL in another locale (locale is stored in session, switched via /lang/{locale}). */
    function locale_switch_url(string $locale): string
    {
        return route('locale.switch', $locale);
    }
}
