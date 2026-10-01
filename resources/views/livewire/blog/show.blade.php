@php
    $encodedUrl = urlencode($shareUrl);
    $encodedTitle = urlencode($post->title);
    $shares = [
        'Facebook' => ['https://www.facebook.com/sharer/sharer.php?u='.$encodedUrl, 'M14 8h3V4h-3a4 4 0 0 0-4 4v2H8v4h2v8h4v-8h3l1-4h-4V8Z'],
        'X' => ['https://twitter.com/intent/tweet?url='.$encodedUrl.'&text='.$encodedTitle, 'M17.7 3h3.1l-6.8 7.7L22 21h-6.2l-4.9-6.4L5.3 21H2.2l7.2-8.3L1.8 3h6.4l4.4 5.9L17.7 3Zm-1.1 16.2h1.7L7.3 4.7H5.5l11.1 14.5Z'],
        'Telegram' => ['https://t.me/share/url?url='.$encodedUrl.'&text='.$encodedTitle, 'M21.5 3.5 2.8 10.7c-1 .4-1 1.8.1 2.1l4.6 1.4 1.8 5.6c.3.9 1.4 1.1 2 .4l2.6-2.7 4.6 3.4c.8.6 1.9.1 2.1-.8L23 4.9c.2-1-.7-1.8-1.5-1.4ZM9.6 14.6l8.2-7.2-6.6 8.6-.4 3-1.2-4.4Z'],
        'WhatsApp' => ['https://wa.me/?text='.$encodedTitle.'%20'.$encodedUrl, 'M17.5 14.4c-.3-.1-1.8-.9-2-1-.3-.1-.5-.1-.7.1-.2.3-.8 1-.9 1.2-.2.2-.3.2-.6.1-.3-.1-1.3-.5-2.4-1.5-.9-.8-1.5-1.8-1.7-2.1-.2-.3 0-.5.1-.6l.4-.5c.2-.2.2-.3.3-.5.1-.2 0-.4 0-.5l-.9-2.2c-.2-.6-.5-.5-.7-.5h-.6c-.2 0-.5.1-.8.4-.3.3-1 1-1 2.5s1.1 2.9 1.2 3.1c.1.2 2.1 3.2 5.1 4.5.7.3 1.3.5 1.7.6.7.2 1.4.2 1.9.1.6-.1 1.8-.7 2-1.4.2-.7.2-1.3.2-1.4-.1-.1-.3-.2-.6-.3ZM12 2a10 10 0 0 0-8.6 15.1L2 22l5-1.3A10 10 0 1 0 12 2Z'],
    ];
@endphp
<div>
    <article>
        <header class="relative isolate overflow-hidden bg-midnight-950 text-white">
            <img src="{{ $post->image }}" alt="" data-fallback="1" data-nofallback onerror="this.style.visibility='hidden'" fetchpriority="high" class="absolute inset-0 -z-10 size-full object-cover opacity-55 animate-slow-zoom">
            <div class="absolute inset-0 -z-10 bg-gradient-to-t from-midnight-950 via-midnight-950/50 to-midnight-950/40"></div>
            <div class="container-x max-w-4xl py-20 text-center sm:py-28 lg:py-36">
                <nav aria-label="Breadcrumb" class="flex items-center justify-center gap-2 text-xs text-white/70 animate-fade-up">
                    <a href="{{ route('blog.index') }}" wire:navigate class="hover:text-gold-300">{{ __('site.nav.blog') }}</a>
                    <x-heroicon-o-chevron-right class="size-3" />
                    <a href="{{ route('blog.index', ['category' => $post->category]) }}" wire:navigate class="hover:text-gold-300">{{ __('site.blog.categories.'.$post->category) }}</a>
                </nav>
                <h1 class="h-display mt-5 animate-fade-up [animation-delay:80ms]">{{ $post->title }}</h1>
                <p class="mt-6 flex flex-wrap items-center justify-center gap-x-4 gap-y-1 text-sm text-white/75 animate-fade-up [animation-delay:160ms]">
                    <time datetime="{{ $post->published_at->toDateString() }}">{{ $post->published_at->translatedFormat('j F Y') }}</time>
                    <span class="size-1 rounded-full bg-gold-300"></span>
                    <span>{{ trans_choice('site.blog.reading_time', $readingTime, ['count' => $readingTime]) }}</span>
                </p>
            </div>
        </header>

        <div class="container-x section grid max-w-6xl gap-12 lg:grid-cols-[1fr_220px]">
            <div class="min-w-0">
                @if ($post->excerpt && ! str_starts_with(trim(strip_tags((string) $post->body)), trim($post->excerpt)))
                    <p class="font-serif text-2xl leading-relaxed text-ink sm:text-3xl">{{ $post->excerpt }}</p>
                    <div class="my-10 h-px w-24 bg-gold-400"></div>
                @endif
                <div class="prose-hotel text-base sm:text-lg">
                    {!! $post->body !!}
                </div>
                <div class="mt-12 flex flex-wrap items-center gap-4 border-t border-line pt-8">
                    <a href="{{ route('blog.index') }}" wire:navigate class="btn-outline btn-sm"><x-heroicon-o-arrow-left class="size-4" /> {{ __('site.blog.back') }}</a>
                </div>
            </div>
            <aside class="lg:sticky lg:top-24 lg:self-start">
                <p class="label">{{ __('site.blog.share') }}</p>
                <div class="mt-3 flex flex-wrap gap-2 lg:flex-col lg:items-start">
                    @foreach ($shares as $network => [$href, $path])
                        <a href="{{ $href }}" target="_blank" rel="noopener" class="flex items-center gap-3 rounded-full border border-line px-4 py-2 text-sm transition hover:border-gold-500 hover:text-gold-600 dark:hover:text-gold-300">
                            <svg class="size-4" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path d="{{ $path }}"/></svg> {{ $network }}
                        </a>
                    @endforeach
                    <button type="button" x-data="{ copied: false }"
                            @click="navigator.clipboard?.writeText(@js($shareUrl)).then(() => { copied = true; $dispatch('notify', { message: @js(__('site.blog.link_copied')) }); setTimeout(() => copied = false, 2000) })"
                            class="flex items-center gap-3 rounded-full border border-line px-4 py-2 text-sm transition hover:border-gold-500 hover:text-gold-600 dark:hover:text-gold-300">
                        <x-heroicon-o-link class="size-4" /> <span x-text="copied ? @js(__('site.offers.copied_short')) : @js(__('site.blog.copy_link'))">{{ __('site.blog.copy_link') }}</span>
                    </button>
                </div>
                <div class="mt-8 hidden rounded-2xl bg-midnight-900 p-6 text-white lg:block">
                    <p class="font-serif text-2xl leading-snug">{{ __('site.blog.aside_title') }}</p>
                    <a href="{{ route('booking') }}" class="btn-gold btn-sm mt-4">{{ __('site.book_now') }}</a>
                </div>
            </aside>
        </div>
    </article>

    @if ($related->isNotEmpty())
        <section class="section bg-elevated/60">
            <div class="container-x">
                <x-section-heading align="left" :eyebrow="__('site.blog.related_eyebrow')" :title="__('site.blog.related_title')" />
                <div class="mt-12 grid gap-10 md:grid-cols-3">
                    @foreach ($related as $item)
                        <x-post-card :post="$item" wire:key="rel-{{ $item->id }}" />
                    @endforeach
                </div>
            </div>
        </section>
    @endif
</div>
