@props([
    'type',                 // App\Models\RoomType
    'quote' => null,        // BookingService::quote() result, when dates are known
    'available' => null,    // free rooms for the dates, when dates are known
    'params' => [],         // check_in, check_out, adults, children forwarded to links
    'amenities' => true,
])
@php
    $bookParams = array_filter(array_merge(['room_type' => $type->slug], $params), fn ($v) => $v !== null && $v !== '');
    $showParams = array_filter(array_merge(['roomType' => $type->slug], $params), fn ($v) => $v !== null && $v !== '');
    $soldOut = $available !== null && $available < 1;
    $tooShort = $quote && $quote['nights'] < ($quote['min_nights'] ?? 1);
@endphp
<article {{ $attributes->merge(['class' => 'group card flex flex-col overflow-hidden transition duration-300 hover:-translate-y-1 hover:shadow-xl hover:shadow-midnight-900/10']) }}>
    <a href="{{ route('rooms.show', $showParams) }}" wire:navigate class="relative block aspect-[4/3] overflow-hidden bg-elevated">
        <img src="{{ $type->cover }}" alt="{{ $type->name }}" loading="lazy" class="size-full object-cover transition duration-700 group-hover:scale-105">
        <div class="absolute inset-x-0 bottom-0 h-1/2 bg-gradient-to-t from-midnight-950/60 to-transparent"></div>
        <div class="absolute top-4 left-4 flex flex-wrap gap-2">
            @if ($type->is_featured)
                <span class="badge bg-white/90 text-midnight-900 backdrop-blur">{{ __('site.rooms.signature') }}</span>
            @endif
            @if ($available !== null && ! $soldOut && $available <= 2)
                <span class="badge bg-rose-500/90 text-white">{{ trans_choice('site.rooms.only_left', $available, ['count' => $available]) }}</span>
            @endif
        </div>
        <div class="absolute bottom-4 left-4 flex items-center gap-3 text-xs font-medium text-white/90">
            <span class="flex items-center gap-1"><x-heroicon-o-arrows-pointing-out class="size-4" /> {{ $type->size_m2 }} m²</span>
            <span class="flex items-center gap-1"><x-heroicon-o-users class="size-4" /> {{ trans_choice('site.rooms.guests_up_to', $type->max_guests, ['count' => $type->max_guests]) }}</span>
        </div>
    </a>
    <div class="flex flex-1 flex-col p-6">
        <h3 class="font-serif text-2xl leading-tight">
            <a href="{{ route('rooms.show', $showParams) }}" wire:navigate class="transition hover:text-gold-600 dark:hover:text-gold-300">{{ $type->name }}</a>
        </h3>
        @if ($type->short)
            <p class="mt-2 line-clamp-2 text-sm leading-relaxed text-muted">{{ $type->short }}</p>
        @endif
        <p class="mt-3 flex flex-wrap items-center gap-x-4 gap-y-1 text-xs text-muted">
            @if ($type->beds)<span class="flex items-center gap-1.5"><x-heroicon-o-moon class="size-4 text-gold-500" />{{ $type->beds }}</span>@endif
            @if ($type->view)<span class="flex items-center gap-1.5"><x-heroicon-o-eye class="size-4 text-gold-500" />{{ $type->view }}</span>@endif
        </p>
        @if ($amenities && $type->relationLoaded('amenities') && $type->amenities->isNotEmpty())
            <ul class="mt-4 flex flex-wrap gap-2" aria-label="{{ __('site.rooms.amenities') }}">
                @foreach ($type->amenities->take(6) as $amenity)
                    <li class="grid size-9 place-items-center rounded-full bg-elevated text-gold-600 dark:text-gold-300" title="{{ $amenity->name }}">
                        <x-hotel-icon :name="$amenity->icon" class="size-4" /><span class="sr-only">{{ $amenity->name }}</span>
                    </li>
                @endforeach
                @if ($type->amenities->count() > 6)
                    <li class="grid h-9 place-items-center rounded-full bg-elevated px-3 text-xs font-semibold text-muted">+{{ $type->amenities->count() - 6 }}</li>
                @endif
            </ul>
        @endif

        <div class="mt-auto pt-6"><div class="flex items-end justify-between gap-4 border-t border-line pt-5">
            <div>
                @if ($quote && ! $soldOut && ! $tooShort)
                    <p class="text-xs text-muted">{{ trans_choice('site.rooms.total_for_nights', $quote['nights'], ['count' => $quote['nights']]) }}</p>
                    <p class="font-serif text-3xl leading-none">{{ money($quote['total']) }}</p>
                    <p class="mt-1 text-xs text-muted">{{ __('site.rooms.avg_per_night', ['price' => money($quote['avg_nightly'])]) }}</p>
                @elseif ($soldOut)
                    <p class="font-serif text-xl text-rose-600 dark:text-rose-400">{{ __('site.rooms.sold_out') }}</p>
                    <p class="text-xs text-muted">{{ __('site.rooms.try_other_dates') }}</p>
                @elseif ($tooShort)
                    <p class="text-sm font-semibold text-gold-700 dark:text-gold-300">{{ trans_choice('site.rooms.min_nights', $quote['min_nights'], ['count' => $quote['min_nights']]) }}</p>
                @else
                    <p class="text-xs uppercase tracking-wider text-muted">{{ __('site.rooms.from') }}</p>
                    <p class="font-serif text-3xl leading-none">{{ money($type->base_price) }}<span class="font-sans text-sm text-muted"> / {{ __('site.rooms.night') }}</span></p>
                @endif
            </div>
            <div class="flex shrink-0 gap-2">
                @if ($soldOut)
                    <a href="{{ route('rooms.show', $showParams) }}" wire:navigate class="btn-outline btn-sm">{{ __('site.rooms.details') }}</a>
                @else
                    <a href="{{ route('booking', $bookParams) }}" class="btn-gold btn-sm">{{ __('site.rooms.book') }}</a>
                @endif
            </div>
        </div></div>
    </div>
</article>
