@php $images = array_values($roomType->images ?? []); @endphp
<div x-data="{
        images: @js($images),
        active: 0,
        lightbox: false,
        open(i) { this.active = i; this.lightbox = true },
        next() { this.active = (this.active + 1) % this.images.length },
        prev() { this.active = (this.active - 1 + this.images.length) % this.images.length },
     }"
     @keydown.escape.window="lightbox = false"
     @keydown.arrow-right.window="lightbox && next()"
     @keydown.arrow-left.window="lightbox && prev()">

    <div class="container-x pt-8 sm:pt-10">
        {{-- Breadcrumbs --}}
        <nav aria-label="Breadcrumb" class="flex items-center gap-2 text-xs text-muted">
            <a href="{{ route('home') }}" wire:navigate class="hover:text-gold-600">{{ __('site.common.home') }}</a>
            <x-heroicon-o-chevron-right class="size-3" />
            <a href="{{ route('rooms.index') }}" wire:navigate class="hover:text-gold-600">{{ __('site.nav.rooms') }}</a>
            <x-heroicon-o-chevron-right class="size-3" />
            <span class="text-ink">{{ $roomType->name }}</span>
        </nav>

        <div class="mt-5 flex flex-col justify-between gap-4 md:flex-row md:items-end">
            <div>
                @if ($roomType->is_featured)<span class="badge-gold mb-3">{{ __('site.rooms.signature') }}</span>@endif
                <h1 class="h-display">{{ $roomType->name }}</h1>
                @if ($roomType->short)<p class="lead mt-3 max-w-2xl">{{ $roomType->short }}</p>@endif
            </div>
            @if ($reviewCount)
                <a href="#reviews" class="flex shrink-0 items-center gap-3">
                    <span class="font-serif text-4xl">{{ number_format($rating, 1) }}</span>
                    <span>
                        <x-star-rating :rating="$rating" />
                        <span class="block text-xs text-muted">{{ trans_choice('site.home.based_on_reviews', $reviewCount, ['count' => $reviewCount]) }}</span>
                    </span>
                </a>
            @endif
        </div>

        {{-- Gallery --}}
        @if (count($images))
            <div class="mt-8 grid gap-3 sm:grid-cols-4 sm:grid-rows-2 sm:h-[460px] lg:h-[540px]">
                <button type="button" @click="open(active)" class="group relative overflow-hidden rounded-3xl bg-elevated aspect-[4/3] sm:col-span-3 sm:row-span-2 sm:aspect-auto">
                    <img :src="images[active]" src="{{ $images[0] }}" alt="{{ $roomType->name }}" fetchpriority="high" class="size-full object-cover transition duration-700 group-hover:scale-[1.03]">
                    <span class="btn-sm absolute right-4 bottom-4 inline-flex items-center gap-2 rounded-full bg-white/90 px-4 py-2 text-xs font-semibold text-midnight-900 shadow backdrop-blur">
                        <x-heroicon-o-arrows-pointing-out class="size-4" /> {{ trans_choice('site.room.view_photos', count($images), ['count' => count($images)]) }}
                    </span>
                </button>
                <div class="grid grid-cols-4 gap-3 sm:col-span-1 sm:row-span-2 sm:grid-cols-1 sm:grid-rows-3">
                    @foreach (array_slice($images, 0, 3) as $i => $src)
                        <button type="button" @click="active = {{ $i }}" @dblclick="open({{ $i }})"
                                :class="active === {{ $i }} ? 'ring-2 ring-gold-400 ring-offset-2 ring-offset-page' : 'opacity-80 hover:opacity-100'"
                                class="relative aspect-square overflow-hidden rounded-2xl bg-elevated transition sm:aspect-auto" aria-label="{{ __('site.room.photo_n', ['n' => $i + 1]) }}">
                            <img src="{{ $src }}" alt="" loading="lazy" class="size-full object-cover">
                        </button>
                    @endforeach
                </div>
            </div>
        @endif
    </div>

    <div class="container-x section pt-12 sm:pt-16">
        <div class="grid gap-12 lg:grid-cols-[1fr_400px] lg:gap-16">
            <div class="min-w-0">
                {{-- Facts --}}
                <dl class="grid grid-cols-2 gap-4 sm:grid-cols-4">
                    @foreach ([
                        ['icon' => 'arrows-pointing-out', 'label' => __('site.room.size'), 'value' => $roomType->size_m2.' m²'],
                        ['icon' => 'users', 'label' => __('site.room.guests'), 'value' => trans_choice('site.room.adults_n', $roomType->max_adults, ['count' => $roomType->max_adults]).($roomType->max_children ? ' + '.trans_choice('site.room.children_n', $roomType->max_children, ['count' => $roomType->max_children]) : '')],
                        ['icon' => 'moon', 'label' => __('site.room.beds'), 'value' => $roomType->beds ?: '—'],
                        ['icon' => 'eye', 'label' => __('site.room.view'), 'value' => $roomType->view ?: '—'],
                    ] as $fact)
                        <div class="card p-5">
                            <x-hotel-icon :name="$fact['icon']" class="size-6 text-gold-500" />
                            <dt class="mt-3 text-xs font-semibold uppercase tracking-wider text-muted">{{ $fact['label'] }}</dt>
                            <dd class="mt-1 font-semibold leading-snug">{{ $fact['value'] }}</dd>
                        </div>
                    @endforeach
                </dl>

                {{-- Description --}}
                <div class="mt-12">
                    <h2 class="font-serif text-3xl">{{ __('site.room.about') }}</h2>
                    <div class="prose-hotel mt-4 text-base">
                        @foreach (preg_split('/\n\s*\n/', trim((string) $roomType->description)) as $paragraph)
                            @if (trim($paragraph) !== '')<p>{{ $paragraph }}</p>@endif
                        @endforeach
                    </div>
                </div>

                {{-- Amenities --}}
                @if ($roomType->amenities->isNotEmpty())
                    <div class="mt-12">
                        <h2 class="font-serif text-3xl">{{ __('site.room.amenities') }}</h2>
                        <ul class="mt-6 grid gap-x-6 gap-y-4 sm:grid-cols-2 lg:grid-cols-3">
                            @foreach ($roomType->amenities as $amenity)
                                <li class="flex items-center gap-3 text-sm">
                                    <span class="grid size-10 shrink-0 place-items-center rounded-full bg-elevated text-gold-600 dark:text-gold-300"><x-hotel-icon :name="$amenity->icon" class="size-5" /></span>
                                    {{ $amenity->name }}
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                {{-- Policies --}}
                <div class="mt-12 grid gap-4 sm:grid-cols-3">
                    <div class="rounded-2xl bg-elevated p-5">
                        <x-heroicon-o-clock class="size-6 text-gold-500" />
                        <p class="mt-3 text-sm font-semibold">{{ __('site.room.check_in_out') }}</p>
                        <p class="mt-1 text-sm text-muted">{{ __('site.room.check_in_out_text', ['in' => setting('check_in_time'), 'out' => setting('check_out_time')]) }}</p>
                    </div>
                    <div class="rounded-2xl bg-elevated p-5">
                        <x-heroicon-o-shield-check class="size-6 text-gold-500" />
                        <p class="mt-3 text-sm font-semibold">{{ __('site.room.cancellation') }}</p>
                        <p class="mt-1 text-sm text-muted">{{ __('site.room.cancellation_text', ['hours' => setting('free_cancellation_hours')]) }}</p>
                    </div>
                    <div class="rounded-2xl bg-elevated p-5">
                        <x-heroicon-o-moon class="size-6 text-gold-500" />
                        <p class="mt-3 text-sm font-semibold">{{ __('site.room.min_stay') }}</p>
                        <p class="mt-1 text-sm text-muted">{{ trans_choice('site.rooms.min_nights', $roomType->min_nights, ['count' => $roomType->min_nights]) }}</p>
                    </div>
                </div>
            </div>

            {{-- Booking widget --}}
            <aside class="lg:sticky lg:top-24 lg:self-start" id="book">
                <livewire:rooms.booking-widget :room-type="$roomType" />
            </aside>
        </div>
    </div>

    {{-- Reviews --}}
    <section id="reviews" class="section scroll-mt-20 bg-elevated/60">
        <div class="container-x">
            <div class="flex flex-col items-start justify-between gap-6 md:flex-row md:items-end">
                <x-section-heading align="left" :eyebrow="__('site.room.reviews_eyebrow')" :title="__('site.room.reviews_title', ['room' => $roomType->name])" />
                <a href="{{ route('reviews') }}" wire:navigate class="btn-outline shrink-0">{{ __('site.home.all_reviews') }}</a>
            </div>
            @if ($reviews->isNotEmpty())
                <div class="mt-10 grid gap-6 md:grid-cols-2">
                    @foreach ($reviews as $review)
                        <x-review-card :review="$review" wire:key="rv-{{ $review->id }}" />
                    @endforeach
                </div>
            @else
                <x-empty-state class="mt-10" icon="chat-bubble-left-right" :title="__('site.room.no_reviews')" :text="__('site.room.no_reviews_text')" />
            @endif
        </div>
    </section>

    {{-- Other rooms --}}
    @if ($others->isNotEmpty())
        <section class="section" x-data="{ scroll(dir) { $refs.track.scrollBy({ left: dir * $refs.track.clientWidth * 0.8, behavior: 'smooth' }) } }">
            <div class="container-x">
                <div class="flex items-end justify-between gap-6">
                    <x-section-heading align="left" :eyebrow="__('site.room.others_eyebrow')" :title="__('site.room.others_title')" />
                    <div class="hidden gap-2 sm:flex">
                        <button type="button" @click="scroll(-1)" class="grid size-12 place-items-center rounded-full border border-line transition hover:border-gold-500 hover:text-gold-600" aria-label="{{ __('site.common.previous') }}"><x-heroicon-o-arrow-left class="size-5" /></button>
                        <button type="button" @click="scroll(1)" class="grid size-12 place-items-center rounded-full border border-line transition hover:border-gold-500 hover:text-gold-600" aria-label="{{ __('site.common.next') }}"><x-heroicon-o-arrow-right class="size-5" /></button>
                    </div>
                </div>
                <div x-ref="track" class="-mx-4 mt-10 flex snap-x snap-mandatory gap-6 overflow-x-auto scroll-smooth px-4 pb-4 [scrollbar-width:none] sm:mx-0 sm:px-0 [&::-webkit-scrollbar]:hidden">
                    @foreach ($others as $other)
                        <x-room-card :type="$other" :amenities="false" wire:key="other-{{ $other->id }}" class="w-[85%] shrink-0 snap-start sm:w-[calc(50%-12px)] lg:w-[calc(33.333%-16px)]" />
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- Lightbox --}}
    <template x-teleport="body">
        <div x-show="lightbox" x-cloak x-transition.opacity class="fixed inset-0 z-[70] flex items-center justify-center bg-midnight-950/95 p-4" role="dialog" aria-modal="true" aria-label="{{ $roomType->name }}" @click.self="lightbox = false">
            <button type="button" @click="lightbox = false" class="absolute top-4 right-4 grid size-12 place-items-center rounded-full text-white/80 hover:bg-white/10 hover:text-white" aria-label="{{ __('site.common.close') }}"><x-heroicon-o-x-mark class="size-7" /></button>
            <button type="button" @click="prev()" class="absolute left-2 grid size-12 place-items-center rounded-full text-white/80 hover:bg-white/10 hover:text-white sm:left-6" aria-label="{{ __('site.common.previous') }}"><x-heroicon-o-chevron-left class="size-8" /></button>
            <img :src="images[active]" alt="{{ $roomType->name }}" class="max-h-[85vh] max-w-full rounded-2xl object-contain shadow-2xl">
            <button type="button" @click="next()" class="absolute right-2 grid size-12 place-items-center rounded-full text-white/80 hover:bg-white/10 hover:text-white sm:right-6" aria-label="{{ __('site.common.next') }}"><x-heroicon-o-chevron-right class="size-8" /></button>
            <p class="absolute bottom-5 text-sm text-white/70"><span x-text="active + 1"></span> / <span x-text="images.length"></span></p>
        </div>
    </template>
</div>
