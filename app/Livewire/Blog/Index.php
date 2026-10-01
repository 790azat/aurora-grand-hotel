<?php

namespace App\Livewire\Blog;

use App\Models\Post;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public const CATEGORIES = ['news', 'spa', 'dining', 'travel', 'events'];

    #[Url(except: '')]
    public string $category = '';

    public function setCategory(string $category): void
    {
        $this->category = in_array($category, self::CATEGORIES, true) ? $category : '';
        $this->resetPage();
    }

    public function render()
    {
        $category = in_array($this->category, self::CATEGORIES, true) ? $this->category : '';
        $posts = Post::published()->when($category, fn ($q) => $q->where('category', $category))->paginate(7);

        return view('livewire.blog.index', [
            'posts' => $posts,
            'categories' => Post::published()->reorder()->distinct()->pluck('category')
                ->sortBy(fn ($c) => array_search($c, self::CATEGORIES))->values(),
        ])->title(__('site.blog.meta_title'))
            ->layoutData(['description' => __('site.blog.meta_description')]);
    }
}
