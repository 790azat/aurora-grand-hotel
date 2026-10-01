<?php

namespace App\Filament\Concerns;

/** Resource visible to administrators only. */
trait AdminOnly
{
    public static function canViewAny(): bool
    {
        return (bool) auth()->user()?->isAdmin();
    }
}
