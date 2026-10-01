<?php

namespace App\Filament\Pages;

use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    public function getTitle(): string
    {
        return __('admin.dashboard.title');
    }

    public static function getNavigationLabel(): string
    {
        return __('admin.dashboard.title');
    }

    public function getSubheading(): ?string
    {
        $hour = (int) now()->format('H');
        $greeting = $hour < 12 ? 'morning' : ($hour < 18 ? 'afternoon' : 'evening');

        return __('admin.dashboard.greeting_'.$greeting, ['name' => explode(' ', auth()->user()?->name ?? '')[0]])
            .' · '.now()->locale(app()->getLocale())->isoFormat('dddd, D MMMM YYYY');
    }

    public function getColumns(): int|array
    {
        return ['default' => 1, 'lg' => 2];
    }
}
