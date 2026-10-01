<?php

namespace App\Livewire\Pages;

use App\Models\Review;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class Reviews extends Component
{
    use WithPagination;

    #[Url(except: 0)]
    public int $rating = 0;

    public function filter(int $rating): void
    {
        $this->rating = $this->rating === $rating ? 0 : max(0, min(5, $rating));
        $this->resetPage();
    }

    public function render()
    {
        $base = Review::where('is_approved', true);
        $total = (clone $base)->count();
        $distribution = (clone $base)->selectRaw('rating, count(*) as c')->groupBy('rating')->pluck('c', 'rating');

        return view('livewire.pages.reviews', [
            'reviews' => Review::approved()->with('roomType')
                ->when($this->rating, fn ($q) => $q->where('rating', $this->rating))
                ->paginate(6),
            'total' => $total,
            'average' => round((float) (clone $base)->avg('rating'), 1),
            'distribution' => collect([5, 4, 3, 2, 1])->mapWithKeys(fn ($r) => [$r => (int) ($distribution[$r] ?? 0)]),
        ])->title(__('site.reviews.meta_title'))
            ->layoutData(['description' => __('site.reviews.meta_description')]);
    }
}
