<div>
    {{-- Hero --}}
    <section class="relative isolate flex min-h-svh flex-col overflow-hidden bg-midnight-950 text-white">
        <img src="{{ \App\Livewire\Pages\Home::HERO_IMAGE }}" alt="" data-fallback="1" data-nofallback onerror="this.style.visibility='hidden'" fetchpriority="high" class="absolute inset-0 -z-20 size-full object-cover animate-slow-zoom">
        <div class="absolute inset-0 -z-10 bg-gradient-to-b from-midnight-950/75 via-midnight-950/35 to-midnight-950/90"></div>
        <div class="absolute inset-0 -z-10 bg-[radial-gradient(ellipse_at_center,transparent_30%,rgba(7,11,16,0.55)_100%)]"></div>

        <div class="container-x flex flex-1 flex-col justify-center pt-28 pb-10 sm:pt-32">
            <div class="max-w-4xl">
                <p class="eyebrow flex items-center gap-3 text-gold-300 animate-fade-up">
                    <span class="tracking-[0.3em]">★★★★★</span>
                    <span class="h-px w-10 bg-gold-300/60"></span>
                    {{ __('site.home.hero_eyebrow') }}
                </p>
                <h1 class="mt-6 font-serif text-5xl leading-[0.98] font-medium sm:text-7xl lg:text-8xl animate-fade-up [animation-delay:120ms]">
                    {{ __('site.home.hero_title_1') }}<br>
                    <em class="font-normal text-gold-200">{{ __('site.home.hero_title_2') }}</em>
                </h1>
                <p class="mt-6 max-w-xl text-base leading-relaxed text-white/80 sm:text-lg animate-fade-up [animation-delay:240ms]">{{ __('site.home.hero_text') }}</p>
                <div class="mt-8 flex flex-wrap gap-3 animate-fade-up [animation-delay:360ms]">
                    <a href="{{ route('rooms.index') }}" wire:navigate class="btn-gold">{{ __('site.home.explore_rooms') }} <x-heroicon-o-arrow-right class="size-4" /></a>
                    <a href="{{ route('offers') }}" wire:navigate class="btn-ghost-light">{{ __('site.home.see_offers') }}</a>
                </div>
            </div>
        </div>

        <div class="container-x pb-8 sm:pb-12 animate-fade-up [animation-delay:480ms]">
            <x-availability-search />
            <a href="#intro" class="mx-auto mt-6 hidden w-fit flex-col items-center gap-2 text-[10px] font-semibold uppercase tracking-[0.3em] text-white/60 transition hover:text-gold-300 sm:flex">
                {{ __('site.home.scroll') }}
                <span class="h-8 w-px animate-pulse bg-gradient-to-b from-gold-300 to-transparent"></span>
            </a>
        </div>
    </section>

    {{-- Intro + stats --}}
    <section id="intro" class="section scroll-mt-18">
        <div class="container-x grid items-center gap-12 lg:grid-cols-2 lg:gap-20">
            <div>
                <x-section-heading align="left" :eyebrow="__('site.home.intro_eyebrow')" :title="__('site.home.intro_title')" />
                <p class="lead mt-6">{{ __('site.home.intro_text_1') }}</p>
                <p class="mt-4 leading-relaxed text-muted">{{ __('site.home.intro_text_2') }}</p>
                <div class="mt-8 flex flex-wrap items-center gap-6">
                    <a href="{{ route('about') }}" wire:navigate class="btn-dark">{{ __('site.home.our_story') }}</a>
                    <div class="font-serif text-2xl italic text-gold-600 dark:text-gold-300">{{ __('site.about.team.0.name') }}</div>
                </div>
            </div>
            <div class="relative">
                <div class="grid grid-cols-5 gap-4">
                    <img src="https://images.unsplash.com/photo-1542314831-068cd1dbfeeb?auto=format&fit=crop&w=900&q=80" alt="{{ __('site.home.intro_img_1') }}" loading="lazy" class="col-span-3 aspect-[3/4] w-full rounded-3xl object-cover shadow-xl">
                    <div class="col-span-2 flex flex-col gap-4 pt-12">
                        <img src="https://images.unsplash.com/photo-1540541338287-41700207dee6?auto=format&fit=crop&w=700&q=80" alt="{{ __('site.home.intro_img_2') }}" loading="lazy" class="aspect-square w-full rounded-3xl object-cover shadow-xl">
                        <div class="rounded-3xl bg-midnight-900 p-5 text-white dark:border dark:border-line dark:bg-surface dark:text-ink">
                            <p class="font-serif text-4xl">{{ number_format($stats['rating'], 1) }}</p>
                            <x-star-rating :rating="$stats['rating']" size="size-3.5" class="mt-1" />
                            <p class="mt-2 text-xs opacity-75">{{ trans_choice('site.home.based_on_reviews', $reviewCount, ['count' => $reviewCount]) }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="container-x mt-16 sm:mt-24">
            <dl class="grid grid-cols-2 gap-px overflow-hidden rounded-3xl border border-line bg-line lg:grid-cols-4">
                @foreach ([
                    ['value' => $stats['rooms'], 'label' => __('site.home.stat_rooms'), 'icon' => 'key'],
                    ['value' => __('site.home.meters', ['n' => $stats['beach']]), 'label' => __('site.home.stat_beach'), 'icon' => 'sun'],
                    ['value' => $stats['years'], 'label' => __('site.home.stat_years'), 'icon' => 'trophy'],
                    ['value' => number_format($stats['rating'], 1).'/5', 'label' => __('site.home.stat_rating'), 'icon' => 'star'],
                ] as $i => $stat)
                    <div class="flex flex-col items-center gap-2 bg-surface p-6 text-center sm:p-8">
                        <x-hotel-icon :name="$stat['icon']" class="size-6 text-gold-500" />
                        <dd class="font-serif text-4xl sm:text-5xl">{{ $stat['value'] }}</dd>
                        <dt class="text-xs font-semibold uppercase tracking-[0.18em] text-muted">{{ $stat['label'] }}</dt>
                    </div>
                @endforeach
            </dl>
        </div>
    </section>

    {{-- Featured rooms --}}
    <section class="section bg-elevated/60">
        <div class="container-x">
            <div class="flex flex-col items-start justify-between gap-6 md:flex-row md:items-end">
                <x-section-heading align="left" :eyebrow="__('site.home.rooms_eyebrow')" :title="__('site.home.rooms_title')" :subtitle="__('site.home.rooms_text', ['price' => money($minPrice)])" />
                <a href="{{ route('rooms.index') }}" wire:navigate class="btn-outline shrink-0">{{ trans_choice('site.home.all_rooms', $roomTypeCount, ['count' => $roomTypeCount]) }} <x-heroicon-o-arrow-right class="size-4" /></a>
            </div>
            <div class="mt-12 grid gap-8 md:grid-cols-2 lg:grid-cols-3">
                @foreach ($featured as $type)
                    <x-room-card :type="$type" wire:key="room-{{ $type->id }}" />
                @endforeach
            </div>
        </div>
    </section>

    {{-- Experiences --}}
    <section class="section">
        <div class="container-x">
            <x-section-heading :eyebrow="__('site.home.experiences_eyebrow')" :title="__('site.home.experiences_title')" :subtitle="__('site.home.experiences_text')" />
            <div class="mt-12 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($facilities as $i => $facility)
                    <a href="{{ route('facilities') }}#{{ $facility->slug }}" wire:navigate
                       class="group relative isolate flex aspect-[4/5] flex-col justify-end overflow-hidden rounded-3xl bg-midnight-900 p-6 text-white sm:aspect-[4/4.6]">
                        <img src="{{ $facility->image }}" alt="" loading="lazy" class="absolute inset-0 -z-10 size-full object-cover transition duration-700 group-hover:scale-105">
                        <div class="absolute inset-0 -z-10 bg-gradient-to-t from-midnight-950/90 via-midnight-950/30 to-transparent transition group-hover:from-midnight-950"></div>
                        <span class="grid size-11 place-items-center rounded-full border border-white/30 bg-white/10 backdrop-blur"><x-hotel-icon :name="$facility->icon" class="size-5 text-gold-200" /></span>
                        <h3 class="mt-4 font-serif text-3xl">{{ $facility->name }}</h3>
                        <p class="mt-2 line-clamp-2 max-w-sm text-sm text-white/75 transition-all duration-500 lg:max-h-0 lg:opacity-0 lg:group-hover:max-h-20 lg:group-hover:opacity-100">{{ $facility->description }}</p>
                        @if ($facility->hours)
                            <p class="mt-3 flex items-center gap-1.5 text-xs font-semibold uppercase tracking-wider text-gold-200"><x-heroicon-o-clock class="size-4" /> {{ $facility->hours === 'On request' ? __('site.facilities.on_request') : $facility->hours }}</p>
                        @endif
                    </a>
                @endforeach
            </div>
            <div class="mt-10 text-center">
                <a href="{{ route('facilities') }}" wire:navigate class="btn-outline">{{ __('site.home.all_experiences') }} <x-heroicon-o-arrow-right class="size-4" /></a>
            </div>
        </div>
    </section>

    {{-- Offers --}}
    @if ($offers->isNotEmpty())
        <section class="section relative isolate overflow-hidden bg-midnight-950 text-white">
            <div class="absolute -top-40 -right-40 -z-10 size-[30rem] rounded-full bg-gold-500/10 blur-3xl"></div>
            <div class="container-x">
                <div class="flex flex-col items-start justify-between gap-6 md:flex-row md:items-end">
                    <x-section-heading light align="left" :eyebrow="__('site.home.offers_eyebrow')" :title="__('site.home.offers_title')" :subtitle="__('site.home.offers_text')" />
                    <a href="{{ route('offers') }}" wire:navigate class="btn-ghost-light shrink-0">{{ __('site.home.all_offers') }} <x-heroicon-o-arrow-right class="size-4" /></a>
                </div>
                <div class="mt-12 grid gap-6 text-ink md:grid-cols-2 lg:grid-cols-3">
                    @foreach ($offers as $offer)
                        <x-offer-card :offer="$offer" wire:key="offer-{{ $offer->id }}" class="border-white/10" />
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- Reviews --}}
    <section class="section">
        <div class="container-x" x-data="{ scroll(dir) { $refs.track.scrollBy({ left: dir * $refs.track.clientWidth * 0.8, behavior: 'smooth' }) } }">
            <div class="flex flex-col items-start justify-between gap-8 md:flex-row md:items-end">
                <div class="flex items-end gap-6">
                    <x-section-heading align="left" :eyebrow="__('site.home.reviews_eyebrow')" :title="__('site.home.reviews_title')" />
                </div>
                <div class="flex items-center gap-6">
                    <div class="text-right">
                        <p class="font-serif text-5xl leading-none">{{ number_format($rating, 1) }}</p>
                        <x-star-rating :rating="$rating" class="mt-1" />
                        <p class="mt-1 text-xs text-muted">{{ trans_choice('site.home.based_on_reviews', $reviewCount, ['count' => $reviewCount]) }}</p>
                    </div>
                    <div class="hidden gap-2 sm:flex">
                        <button type="button" @click="scroll(-1)" class="grid size-12 place-items-center rounded-full border border-line transition hover:border-gold-500 hover:text-gold-600" aria-label="{{ __('site.common.previous') }}"><x-heroicon-o-arrow-left class="size-5" /></button>
                        <button type="button" @click="scroll(1)" class="grid size-12 place-items-center rounded-full border border-line transition hover:border-gold-500 hover:text-gold-600" aria-label="{{ __('site.common.next') }}"><x-heroicon-o-arrow-right class="size-5" /></button>
                    </div>
                </div>
            </div>
            <div x-ref="track" class="-mx-4 mt-12 flex snap-x snap-mandatory gap-6 overflow-x-auto scroll-smooth px-4 pb-4 [scrollbar-width:none] sm:mx-0 sm:px-0 [&::-webkit-scrollbar]:hidden">
                @foreach ($reviews as $review)
                    <x-review-card :review="$review" show-room class="w-[85%] shrink-0 snap-start sm:w-[calc(50%-12px)] lg:w-[calc(33.333%-16px)]" wire:key="review-{{ $review->id }}" />
                @endforeach
            </div>
            <div class="mt-8 text-center">
                <a href="{{ route('reviews') }}" wire:navigate class="link text-sm font-semibold">{{ __('site.home.all_reviews') }} →</a>
            </div>
        </div>
    </section>

    {{-- Gallery teaser --}}
    @if ($gallery->count() >= 5)
        <section class="section bg-elevated/60">
            <div class="container-x">
                <div class="flex flex-col items-start justify-between gap-6 md:flex-row md:items-end">
                    <x-section-heading align="left" :eyebrow="__('site.home.gallery_eyebrow')" :title="__('site.home.gallery_title')" />
                    <a href="{{ route('gallery') }}" wire:navigate class="btn-outline shrink-0">{{ __('site.home.open_gallery') }} <x-heroicon-o-arrow-right class="size-4" /></a>
                </div>
                <div class="mt-12 grid auto-rows-[160px] grid-cols-2 gap-4 sm:auto-rows-[220px] lg:grid-cols-4">
                    @foreach ($gallery as $i => $image)
                        <a href="{{ route('gallery') }}" wire:navigate class="group relative overflow-hidden rounded-2xl bg-midnight-900 {{ $i === 0 ? 'col-span-2 row-span-2' : '' }}">
                            <img src="{{ $image->url }}" alt="{{ $image->caption }}" loading="lazy" class="size-full object-cover transition duration-700 group-hover:scale-105 group-hover:opacity-80">
                            @if ($image->caption)
                                <span class="absolute inset-x-0 bottom-0 translate-y-full bg-gradient-to-t from-midnight-950/90 to-transparent p-4 text-sm text-white transition duration-300 group-hover:translate-y-0">{{ $image->caption }}</span>
                            @endif
                        </a>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- Journal --}}
    @if ($posts->isNotEmpty())
        <section class="section">
            <div class="container-x">
                <div class="flex flex-col items-start justify-between gap-6 md:flex-row md:items-end">
                    <x-section-heading align="left" :eyebrow="__('site.home.journal_eyebrow')" :title="__('site.home.journal_title')" />
                    <a href="{{ route('blog.index') }}" wire:navigate class="btn-outline shrink-0">{{ __('site.home.all_posts') }} <x-heroicon-o-arrow-right class="size-4" /></a>
                </div>
                <div class="mt-12 grid gap-10 md:grid-cols-3">
                    @foreach ($posts as $post)
                        <x-post-card :post="$post" wire:key="post-{{ $post->id }}" />
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- Location --}}
    <section class="section bg-elevated/60">
        <div class="container-x grid gap-10 lg:grid-cols-[1fr_1.4fr] lg:items-center">
            <div>
                <x-section-heading align="left" :eyebrow="__('site.home.location_eyebrow')" :title="__('site.home.location_title')" :subtitle="__('site.home.location_text')" />
                <ul class="mt-8 space-y-4">
                    @foreach (__('site.home.distances') as $item)
                        <li class="flex items-center justify-between gap-4 border-b border-line pb-4 text-sm">
                            <span class="flex items-center gap-3"><x-hotel-icon :name="$item['icon']" class="size-5 text-gold-500" /> {{ $item['place'] }}</span>
                            <span class="font-semibold">{{ $item['time'] }}</span>
                        </li>
                    @endforeach
                </ul>
                <div class="mt-8 space-y-2 text-sm">
                    <p class="flex items-center gap-3"><x-heroicon-o-map-pin class="size-5 text-gold-500" /> {{ \App\Models\Setting::localized('hotel_address') }}</p>
                    <p class="flex items-center gap-3"><x-heroicon-o-phone class="size-5 text-gold-500" /> <a href="tel:{{ preg_replace('/[^\d+]/', '', setting('hotel_phone')) }}" class="hover:text-gold-600">{{ setting('hotel_phone') }}</a></p>
                </div>
                <a href="{{ route('contact') }}" wire:navigate class="btn-dark mt-8">{{ __('site.home.get_directions') }}</a>
            </div>
            <x-map-embed class="aspect-[4/3] lg:aspect-auto lg:h-[520px]" />
        </div>
    </section>

    {{-- Final CTA --}}
    <section class="relative isolate overflow-hidden bg-midnight-950 py-24 text-center text-white sm:py-32">
        <img src="https://images.unsplash.com/photo-1507525428034-b723cf961d3e?auto=format&fit=crop&w=2000&q=80" alt="" data-fallback="1" data-nofallback onerror="this.style.visibility='hidden'" loading="lazy" class="absolute inset-0 -z-10 size-full object-cover opacity-50">
        <div class="absolute inset-0 -z-10 bg-gradient-to-t from-midnight-950 via-midnight-950/50 to-midnight-950/70"></div>
        <div class="container-x">
            <p class="eyebrow text-gold-300">{{ __('site.home.cta_eyebrow') }}</p>
            <h2 class="h-display mx-auto mt-4 max-w-3xl">{{ __('site.home.cta_title') }}</h2>
            <p class="mx-auto mt-5 max-w-xl text-white/75">{{ __('site.home.cta_text') }}</p>
            <div class="mt-8 flex flex-wrap justify-center gap-3">
                <a href="{{ route('booking') }}" class="btn-gold">{{ __('site.book_now') }}</a>
                <a href="{{ route('contact') }}" wire:navigate class="btn-ghost-light">{{ __('site.home.cta_contact') }}</a>
            </div>
            <ul class="mx-auto mt-10 flex max-w-2xl flex-wrap justify-center gap-x-8 gap-y-3 text-sm text-white/80">
                @foreach (__('site.home.cta_perks') as $perk)
                    <li class="flex items-center gap-2"><x-heroicon-o-check-circle class="size-5 text-gold-300" /> {{ $perk }}</li>
                @endforeach
            </ul>
        </div>
    </section>
</div>
