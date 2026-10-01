<?php

namespace App\Filament\Concerns;

/** Resource visible to admins and managers only. */
trait ManagerOnly
{
    public static function canViewAny(): bool
    {
        return (bool) auth()->user()?->isManager();
    }
}
