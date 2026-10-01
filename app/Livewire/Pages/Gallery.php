<?php

namespace App\Livewire\Pages;

use App\Models\GalleryImage;
use Livewire\Component;

class Gallery extends Component
{
    public const CATEGORIES = ['hotel', 'rooms', 'spa', 'dining', 'beach'];

    public function render()
    {
        $images = GalleryImage::orderBy('sort')->orderBy('id')->get();

        return view('livewire.pages.gallery', [
            'images' => $images->map(fn (GalleryImage $i) => [
                'id' => $i->id,
                'url' => $i->url,
                'caption' => $i->caption,
                'category' => $i->category,
            ])->values(),
            'categories' => collect(self::CATEGORIES)->filter(fn ($c) => $images->contains('category', $c))->values(),
        ])->title(__('site.gallery.meta_title'))
            ->layoutData(['description' => __('site.gallery.meta_description')]);
    }
}
