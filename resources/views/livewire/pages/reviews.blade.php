<div>
    <x-page-hero :eyebrow="__('site.reviews.eyebrow')" :title="__('site.reviews.title')" :subtitle="__('site.reviews.subtitle')" size="sm"
                 image="https://images.unsplash.com/photo-1571896349842-33c89424de2d?auto=format&fit=crop&w=2000&q=80" />

    <section id="reviews-list" class="section scroll-mt-20">
        <div class="container-x grid gap-10 lg:grid-cols-[320px_1fr] lg:gap-14">
            <aside class="lg:sticky lg:top-24 lg:self-start">
                <div class="card p-7">
                    <p class="font-serif text-7xl leading-none">{{ number_format($average, 1) }}</p>
                    <x-star-rating :rating="$average" size="size-5" class="mt-3" />
                    <p class="mt-2 text-sm text-muted">{{ trans_choice('site.home.based_on_reviews', $total, ['count' => $total]) }}</p>

                    <ul class="mt-6 space-y-2">
                        @foreach ($distribution as $stars => $count)
                            @php $pct = $total ? round($count / $total * 100) : 0; @endphp
                            <li>
                                <button type="button" wire:click="filter({{ $stars }})" @disabled(! $count)
                                        class="group flex w-full items-center gap-3 rounded-lg px-2 py-1.5 text-sm transition hover:bg-elevated disabled:cursor-default disabled:hover:bg-transparent {{ $rating === $stars ? 'bg-elevated ring-1 ring-gold-400' : '' }}"
                                        aria-pressed="{{ $rating === $stars ? 'true' : 'false' }}" aria-label="{{ trans_choice('site.reviews.filter_stars', $stars, ['count' => $stars]) }}">
                                    <span class="flex w-8 items-center gap-0.5 font-semibold">{{ $stars }}<x-heroicon-s-star class="size-3.5 text-gold-400" /></span>
                                    <span class="h-2 flex-1 overflow-hidden rounded-full bg-elevated group-hover:bg-line">
                                        <span class="block h-full rounded-full bg-gold-400 transition-all" style="width: {{ $pct }}%"></span>
                                    </span>
                                    <span class="w-8 text-right text-xs text-muted">{{ $count }}</span>
                                </button>
                            </li>
                        @endforeach
                    </ul>
                    @if ($rating)
                        <button type="button" wire:click="filter(0)" class="link mt-4 text-sm">{{ __('site.reviews.show_all') }}</button>
                    @endif
                </div>

                <div class="mt-6 rounded-3xl bg-midnight-900 p-7 text-white">
                    <x-heroicon-o-pencil-square class="size-8 text-gold-300" />
                    <h2 class="mt-4 font-serif text-2xl">{{ __('site.reviews.leave_title') }}</h2>
                    <p class="mt-2 text-sm text-white/70">{{ __('site.reviews.leave_text') }}</p>
                    <a href="{{ route('account.dashboard') }}" class="btn-gold btn-sm mt-5">{{ __('site.reviews.go_account') }}</a>
                </div>
            </aside>

            <div>
                <div wire:loading.delay.class="opacity-50" class="grid gap-6 transition-opacity md:grid-cols-2">
                    @forelse ($reviews as $review)
                        <x-review-card :review="$review" show-room wire:key="review-{{ $review->id }}" />
                    @empty
                        <x-empty-state class="md:col-span-2" icon="chat-bubble-left-right" :title="__('site.reviews.empty_title')" :text="__('site.reviews.empty_text')" />
                    @endforelse
                </div>
                <div class="mt-10">
                    {{ $reviews->links('components.pagination', ['scrollTo' => 'reviews-list']) }}
                </div>
            </div>
        </div>
    </section>
</div>
