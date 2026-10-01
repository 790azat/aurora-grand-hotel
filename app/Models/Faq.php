<?php

namespace App\Models;

use App\Models\Concerns\HasLocalizedAttributes;
use Illuminate\Database\Eloquent\Model;

class Faq extends Model
{
    use HasLocalizedAttributes;

    protected $guarded = [];

    protected array $localized = ['question', 'answer'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }
}
