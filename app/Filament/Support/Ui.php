<?php

namespace App\Filament\Support;

use App\Models\Booking;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;

/** Shared labels, colors and schema helpers for the admin panel. */
class Ui
{
    public const STATUS_COLORS = [
        'pending' => 'warning',
        'confirmed' => 'info',
        'checked_in' => 'success',
        'checked_out' => 'gray',
        'cancelled' => 'danger',
        'no_show' => 'danger',
    ];

    public const STATUS_ICONS = [
        'pending' => 'heroicon-m-clock',
        'confirmed' => 'heroicon-m-check-badge',
        'checked_in' => 'heroicon-m-key',
        'checked_out' => 'heroicon-m-arrow-right-start-on-rectangle',
        'cancelled' => 'heroicon-m-x-circle',
        'no_show' => 'heroicon-m-user-minus',
    ];

    public const PAYMENT_COLORS = ['unpaid' => 'warning', 'paid' => 'success', 'refunded' => 'gray'];

    public const PAYMENT_METHODS = ['card', 'idram', 'on_arrival', 'cash', 'bank_transfer'];

    public const ROOM_STATUS_COLORS = ['available' => 'success', 'maintenance' => 'warning', 'out_of_order' => 'danger'];

    public const HOUSEKEEPING_COLORS = ['clean' => 'success', 'dirty' => 'danger', 'inspected' => 'info'];

    public const ROLE_COLORS = ['admin' => 'danger', 'manager' => 'warning', 'reception' => 'info', 'guest' => 'gray'];

    /** Translated options for a group, e.g. options('status', Booking::STATUSES). */
    public static function options(string $group, array $keys): array
    {
        return collect($keys)->mapWithKeys(fn ($k) => [$k => __("admin.{$group}.{$k}")])->all();
    }

    public static function label(string $group, ?string $key): string
    {
        return $key === null || $key === '' ? '—' : __("admin.{$group}.{$key}");
    }

    public static function statusOptions(): array
    {
        return self::options('status', Booking::STATUSES);
    }

    public static function paymentStatusOptions(): array
    {
        return self::options('payment_status', Booking::PAYMENT_STATUSES);
    }

    public static function paymentMethodOptions(): array
    {
        return self::options('payment_method', self::PAYMENT_METHODS);
    }

    public static function sourceOptions(): array
    {
        return self::options('source', Booking::SOURCES);
    }

    public static function currency(): string
    {
        return (string) setting('currency', 'USD');
    }

    /**
     * EN / RU / HY tabs for translated fields.
     *
     * @param  callable(string $locale): array  $fields
     */
    public static function localeTabs(callable $fields, string $key = 'translations'): Tabs
    {
        return Tabs::make($key)
            ->tabs([
                Tab::make('en')->label('English')->schema($fields('en')),
                Tab::make('ru')->label('Русский')->schema($fields('ru')),
                Tab::make('hy')->label('Հայերեն')->schema($fields('hy')),
            ])
            ->columnSpanFull();
    }

    /** Suffix for a translated field label, e.g. "Name (EN)". */
    public static function l(string $key, string $locale): string
    {
        return __("admin.fields.{$key}").' ('.strtoupper($locale).')';
    }
}
