<?php

namespace App\Models;

use App\Models\Concerns\HasLocalizedAttributes;
use Illuminate\Database\Eloquent\Model;

class Facility extends Model
{
    use HasLocalizedAttributes;

    protected $guarded = [];

    protected array $localized = ['name', 'description'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }
}
