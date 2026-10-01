<?php

namespace App\Livewire\Pages;

use App\Models\Faq as FaqModel;
use Illuminate\Support\Str;
use Livewire\Attributes\Url;
use Livewire\Component;

class Faq extends Component
{
    #[Url(as: 'q', except: '')]
    public string $search = '';

    public function render()
    {
        $all = FaqModel::where('is_active', true)->orderBy('sort')->get();
        $term = Str::lower(trim($this->search));
        $faqs = $term === '' ? $all : $all->filter(
            fn (FaqModel $f) => Str::contains(Str::lower($f->question.' '.strip_tags($f->answer)), $term)
        )->values();

        return view('livewire.pages.faq', ['faqs' => $faqs, 'total' => $all->count()])
            ->title(__('site.faq.meta_title'))
            ->layoutData(['description' => __('site.faq.meta_description')]);
    }
}
