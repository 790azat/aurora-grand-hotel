<?php

namespace App\Models;

use App\Models\Concerns\HasLocalizedAttributes;
use Illuminate\Database\Eloquent\Model;

class GalleryImage extends Model
{
    use HasLocalizedAttributes;

    protected $guarded = [];

    protected array $localized = ['caption'];
}
