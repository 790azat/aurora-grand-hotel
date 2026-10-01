<div>
    <x-page-hero :eyebrow="__('site.facilities.eyebrow')" :title="__('site.facilities.title')" :subtitle="__('site.facilities.subtitle')"
                 image="https://images.unsplash.com/photo-1544161515-4ab6ce6db874?auto=format&fit=crop&w=2000&q=80" />

    {{-- Quick nav --}}
    <nav class="border-b border-line bg-surface" aria-label="{{ __('site.facilities.title') }}">
        <div class="container-x flex gap-2 overflow-x-auto py-4 [scrollbar-width:none]">
            @foreach ($facilities as $facility)
                <a href="#{{ $facility->slug }}" class="flex shrink-0 items-center gap-2 rounded-full border border-line px-4 py-2 text-sm transition hover:border-gold-500 hover:text-gold-600 dark:hover:text-gold-300">
                    <x-hotel-icon :name="$facility->icon" class="size-4 text-gold-500" /> {{ $facility->name }}
                </a>
            @endforeach
        </div>
    </nav>

    <section class="section">
        <div class="container-x space-y-20 sm:space-y-28">
            @forelse ($facilities as $i => $facility)
                <article id="{{ $facility->slug }}" class="grid scroll-mt-28 items-center gap-8 lg:grid-cols-2 lg:gap-16">
                    <div class="relative {{ $i % 2 ? 'lg:order-2' : '' }}">
                        <div class="overflow-hidden rounded-3xl bg-elevated shadow-xl">
                            <img src="{{ $facility->image }}" alt="{{ $facility->name }}" loading="lazy" class="aspect-[4/3] w-full object-cover transition duration-700 hover:scale-105">
                        </div>
                        <span class="absolute -bottom-6 {{ $i % 2 ? 'left-6 lg:-left-6' : 'right-6 lg:-right-6' }} grid size-20 place-items-center rounded-full bg-midnight-900 text-gold-300 shadow-xl ring-8 ring-page">
                            <x-hotel-icon :name="$facility->icon" class="size-8" />
                        </span>
                    </div>
                    <div class="{{ $i % 2 ? 'lg:order-1' : '' }}">
                        <p class="eyebrow">{{ str_pad($i + 1, 2, '0', STR_PAD_LEFT) }} / {{ str_pad($facilities->count(), 2, '0', STR_PAD_LEFT) }}</p>
                        <h2 class="h-section mt-3">{{ $facility->name }}</h2>
                        <p class="lead mt-5">{{ $facility->description }}</p>
                        @if ($facility->hours)
                            <div class="mt-6 inline-flex items-center gap-3 rounded-2xl border border-line bg-surface px-5 py-3">
                                <x-heroicon-o-clock class="size-5 text-gold-500" />
                                <div>
                                    <p class="text-[11px] font-semibold uppercase tracking-wider text-muted">{{ __('site.facilities.hours') }}</p>
                                    <p class="font-semibold">{{ $facility->hours === 'On request' ? __('site.facilities.on_request') : $facility->hours }}</p>
                                </div>
                            </div>
                        @endif
                        <div class="mt-8 flex flex-wrap gap-3">
                            <a href="{{ route('contact') }}" wire:navigate class="btn-outline btn-sm">{{ __('site.facilities.enquire') }}</a>
                        </div>
                    </div>
                </article>
            @empty
                <x-empty-state :title="__('site.facilities.empty')" />
            @endforelse
        </div>
    </section>

    <section class="bg-midnight-950 py-16 text-white sm:py-20">
        <div class="container-x flex flex-col items-center justify-between gap-6 text-center md:flex-row md:text-left">
            <div>
                <h2 class="font-serif text-3xl sm:text-4xl">{{ __('site.facilities.cta_title') }}</h2>
                <p class="mt-2 text-white/70">{{ __('site.facilities.cta_text') }}</p>
            </div>
            <a href="{{ route('booking') }}" class="btn-gold shrink-0">{{ __('site.book_now') }}</a>
        </div>
    </section>
</div>
