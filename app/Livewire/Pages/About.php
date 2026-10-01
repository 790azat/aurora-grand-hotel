<?php

namespace App\Livewire\Pages;

use App\Models\Review;
use App\Models\Room;
use Livewire\Component;

class About extends Component
{
    public function render()
    {
        return view('livewire.pages.about', [
            'stats' => [
                ['value' => (int) now()->year - 1998, 'label' => __('site.home.stat_years')],
                ['value' => Room::count(), 'label' => __('site.home.stat_rooms')],
                ['value' => '320+', 'label' => __('site.about.stat_team')],
                ['value' => number_format((float) Review::approved()->avg('rating') ?: 4.9, 1), 'label' => __('site.home.stat_rating')],
            ],
        ])->title(__('site.about.meta_title'))
            ->layoutData(['description' => __('site.about.meta_description')]);
    }
}
