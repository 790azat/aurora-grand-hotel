<?php

namespace App\Livewire\Pages;

use App\Models\Facility;
use Livewire\Component;

class Facilities extends Component
{
    public function render()
    {
        return view('livewire.pages.facilities', [
            'facilities' => Facility::where('is_active', true)->orderBy('sort')->get(),
        ])->title(__('site.facilities.meta_title'))
            ->layoutData(['description' => __('site.facilities.meta_description')]);
    }
}
