<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class Setting extends Model
{
    protected $guarded = [];

    protected static ?array $cache = null;

    public const DEFAULTS = [
        'hotel_name' => 'Aurora Grand Hotel & Spa',
        'hotel_tagline_en' => 'Where the sea meets timeless luxury',
        'hotel_tagline_ru' => 'Там, где море встречается с вечной роскошью',
        'hotel_tagline_hy' => 'Այնտեղ, ուր ծովը հանդիպում է հավերժական շքեղությանը',
        'hotel_email' => 'reservations@auroragrand.example',
        'hotel_phone' => '+1 (555) 014-2026',
        'hotel_address_en' => '12 Seaside Avenue, Azure Bay',
        'hotel_address_ru' => 'Приморский проспект 12, Лазурная бухта',
        'hotel_address_hy' => 'Ծովափնյա պողոտա 12, Լազուր ծովածոց',
        'hotel_stars' => '5',
        'currency' => 'USD',
        'currency_symbol' => '$',
        'tax_percent' => '10',
        'idram_amd_rate' => '390',
        'check_in_time' => '14:00',
        'check_out_time' => '12:00',
        'free_cancellation_hours' => '48',
        'map_lat' => '43.5855',
        'map_lng' => '39.7231',
        'instagram' => 'https://instagram.com/',
        'facebook' => 'https://facebook.com/',
        'telegram' => 'https://t.me/',
        'whatsapp' => '15550142026',
    ];

    public static function all_values(): array
    {
        if (static::$cache === null) {
            $stored = [];
            try {
                if (Schema::hasTable('settings')) {
                    $stored = static::query()->pluck('value', 'key')->all();
                }
            } catch (\Throwable) {
                // database not ready yet
            }
            static::$cache = array_merge(self::DEFAULTS, $stored);
        }

        return static::$cache;
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        return static::all_values()[$key] ?? $default;
    }

    /** Localized setting: tries `{key}_{locale}` then `{key}_en`. */
    public static function localized(string $key): ?string
    {
        return static::get($key.'_'.app()->getLocale()) ?? static::get($key.'_en');
    }

    public static function put(string $key, mixed $value): void
    {
        static::updateOrCreate(['key' => $key], ['value' => $value]);
        static::$cache = null;
    }

    public static function flush(): void
    {
        static::$cache = null;
    }
}
