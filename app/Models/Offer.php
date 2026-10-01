<?php

namespace App\Models;

use App\Models\Concerns\HasLocalizedAttributes;
use Illuminate\Database\Eloquent\Model;

class Offer extends Model
{
    use HasLocalizedAttributes;

    protected $guarded = [];

    protected array $localized = ['title', 'description', 'badge'];

    protected function casts(): array
    {
        return ['valid_until' => 'date', 'is_active' => 'boolean'];
    }
}
