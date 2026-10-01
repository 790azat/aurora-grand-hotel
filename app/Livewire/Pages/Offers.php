<?php

namespace App\Livewire\Pages;

use App\Models\Offer;
use Livewire\Component;

class Offers extends Component
{
    public function render()
    {
        return view('livewire.pages.offers', [
            'offers' => Offer::where('is_active', true)
                ->where(fn ($q) => $q->whereNull('valid_until')->orWhereDate('valid_until', '>=', today()))
                ->orderBy('sort')->get(),
        ])->title(__('site.offers.meta_title'))
            ->layoutData(['description' => __('site.offers.meta_description')]);
    }
}
