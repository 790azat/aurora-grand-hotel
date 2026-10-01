<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PromoCode extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return ['valid_from' => 'date', 'valid_until' => 'date', 'is_active' => 'boolean', 'value' => 'decimal:2'];
    }

    public function isUsable(int $nights): bool
    {
        $today = now()->startOfDay();

        return $this->is_active
            && (! $this->valid_from || $this->valid_from->lte($today))
            && (! $this->valid_until || $this->valid_until->gte($today))
            && (! $this->max_uses || $this->used_count < $this->max_uses)
            && (! $this->min_nights || $nights >= $this->min_nights);
    }

    public function discountFor(float $amount): float
    {
        $discount = $this->type === 'percent' ? $amount * $this->value / 100 : (float) $this->value;

        return round(min($discount, $amount), 2);
    }
}
