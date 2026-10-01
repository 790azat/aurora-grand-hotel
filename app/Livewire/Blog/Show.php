<?php

namespace App\Livewire\Blog;

use App\Models\Post;
use Illuminate\Support\Str;
use Livewire\Component;

class Show extends Component
{
    public Post $post;

    public function mount(Post $post): void
    {
        abort_unless($post->is_published && $post->published_at && $post->published_at->lte(now()), 404);
        $this->post = $post;
    }

    public function render()
    {
        $post = $this->post;
        $related = Post::published()->whereKeyNot($post->id)
            ->orderByRaw('category = ? desc', [$post->category])
            ->latest('published_at')->take(3)->get();

        $words = str_word_count(strip_tags((string) $post->body_en));

        return view('livewire.blog.show', [
            'related' => $related,
            'readingTime' => max(1, (int) ceil($words / 200)),
            'shareUrl' => route('blog.show', $post),
        ])->title($post->title)
            ->layoutData([
                'description' => Str::limit(strip_tags((string) ($post->excerpt ?: $post->body)), 155),
                'ogImage' => $post->image,
            ]);
    }
}
