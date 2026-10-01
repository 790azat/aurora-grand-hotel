@props(['post'])
<article {{ $attributes->merge(['class' => 'group flex flex-col']) }}>
    <a href="{{ route('blog.show', $post) }}" wire:navigate class="relative block aspect-[3/2] overflow-hidden rounded-2xl bg-elevated">
        <img src="{{ $post->image }}" alt="{{ $post->title }}" loading="lazy" class="size-full object-cover transition duration-700 group-hover:scale-105">
        <span class="badge absolute top-4 left-4 bg-white/90 text-midnight-900 backdrop-blur">{{ __('site.blog.categories.'.$post->category) }}</span>
    </a>
    <div class="mt-5 flex flex-1 flex-col">
        <time datetime="{{ $post->published_at?->toDateString() }}" class="text-xs font-medium uppercase tracking-wider text-muted">{{ $post->published_at?->translatedFormat('j F Y') }}</time>
        <h3 class="mt-2 font-serif text-2xl leading-snug">
            <a href="{{ route('blog.show', $post) }}" wire:navigate class="transition hover:text-gold-600 dark:hover:text-gold-300">{{ $post->title }}</a>
        </h3>
        <p class="mt-2 line-clamp-3 text-sm leading-relaxed text-muted">{{ $post->excerpt }}</p>
        <a href="{{ route('blog.show', $post) }}" wire:navigate class="mt-4 inline-flex items-center gap-1.5 text-sm font-semibold text-gold-600 dark:text-gold-300">
            {{ __('site.blog.read_more') }} <x-heroicon-o-arrow-right class="size-4 transition group-hover:translate-x-1" />
        </a>
    </div>
</article>
