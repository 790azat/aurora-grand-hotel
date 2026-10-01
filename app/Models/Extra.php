<?php

namespace App\Models;

use App\Models\Concerns\HasLocalizedAttributes;
use Illuminate\Database\Eloquent\Model;

class Extra extends Model
{
    use HasLocalizedAttributes;

    public const PRICING = ['per_stay', 'per_night', 'per_guest_night'];

    protected $guarded = [];

    protected array $localized = ['name', 'description'];

    protected function casts(): array
    {
        return ['price' => 'decimal:2', 'is_active' => 'boolean'];
    }

    /** Quantity multiplier for a stay. */
    public function quantityFor(int $nights, int $guests): int
    {
        return match ($this->pricing) {
            'per_night' => $nights,
            'per_guest_night' => $nights * $guests,
            default => 1,
        };
    }
}
