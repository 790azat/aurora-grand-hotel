<?php

namespace App\Filament;

use Filament\Support\Contracts\HasLabel;

/** Admin navigation groups (order of cases = order in the sidebar). */
enum NavGroup implements HasLabel
{
    case Reservations;
    case Hotel;
    case Pricing;
    case Guests;
    case Content;
    case Inbox;
    case Settings;

    public function getLabel(): string
    {
        return __('admin.nav.'.strtolower($this->name));
    }
}
