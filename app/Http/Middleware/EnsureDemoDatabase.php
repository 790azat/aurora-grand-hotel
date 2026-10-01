<?php

namespace App\Http\Middleware;

use App\Models\Setting;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpFoundation\Response;

/**
 * Keeps the serverless demo database usable:
 *  - migrates + seeds an empty database (fresh Postgres, empty /tmp SQLite);
 *  - shifts all demo dates forward so a database seeded days ago (e.g. the
 *    bundled database/demo.sqlite snapshot) still looks like "today".
 */
class EnsureDemoDatabase
{
    protected static bool $checked = false;

    /** Date/datetime columns moved by the date shift. */
    protected const DATE_COLUMNS = [
        'bookings' => ['check_in', 'check_out', 'confirmed_at', 'cancelled_at', 'checked_in_at', 'checked_out_at', 'created_at', 'updated_at'],
        'payments' => ['created_at', 'updated_at'],
        'reviews' => ['created_at', 'updated_at'],
        'posts' => ['published_at'],
        'contact_messages' => ['created_at'],
        'promo_codes' => ['valid_from', 'valid_until'],
        'offers' => ['valid_until'],
        'seasons' => ['starts_on', 'ends_on'],
    ];

    public function handle(Request $request, Closure $next): Response
    {
        if (! static::$checked && env('DEMO_AUTO_SETUP')) {
            static::$checked = true;
            static::ensure();
        }

        return $next($request);
    }

    public static function ensure(bool $fresh = false): void
    {
        $pg = DB::getDriverName() === 'pgsql';
        if ($pg) {
            DB::select('select pg_advisory_lock(20261001)');
        }

        try {
            if ($fresh || ! Schema::hasTable('settings')) {
                Artisan::call($fresh ? 'migrate:fresh' : 'migrate', ['--force' => true, '--seed' => true]);
            } else {
                static::shiftDates();
            }
        } finally {
            if ($pg) {
                DB::select('select pg_advisory_unlock(20261001)');
            }
        }
    }

    public static function shiftDates(): void
    {
        $seededOn = DB::table('settings')->where('key', 'demo_date')->value('value');
        if (! $seededOn) {
            return;
        }

        $days = (int) Carbon::parse($seededOn)->diffInDays(today(), false);
        if ($days <= 0) {
            return;
        }

        $pg = DB::getDriverName() === 'pgsql';
        DB::transaction(function () use ($days, $pg) {
            foreach (self::DATE_COLUMNS as $table => $columns) {
                $sets = collect($columns)->map(fn ($c) => $pg
                    ? "\"{$c}\" = \"{$c}\" + interval '{$days} days'"
                    : "\"{$c}\" = CASE WHEN length(\"{$c}\") = 10 THEN date(\"{$c}\", '+{$days} days') ELSE datetime(\"{$c}\", '+{$days} days') END"
                )->implode(', ');
                DB::statement("update \"{$table}\" set {$sets}");
            }
            DB::table('settings')->where('key', 'demo_date')->update(['value' => today()->toDateString()]);
        });
        Setting::flush();
    }
}
