@props(['offer'])
<article {{ $attributes->merge(['class' => 'group card flex flex-col overflow-hidden']) }}>
    <div class="relative aspect-[16/10] overflow-hidden bg-elevated">
        <img src="{{ $offer->image }}" alt="{{ $offer->title }}" loading="lazy" class="size-full object-cover transition duration-700 group-hover:scale-105">
        <div class="absolute inset-0 bg-gradient-to-t from-midnight-950/50 to-transparent"></div>
        @if ($offer->badge)
            <span class="absolute top-4 left-4 rounded-full bg-gold-500 px-4 py-1.5 text-sm font-bold text-white shadow-lg">{{ $offer->badge }}</span>
        @endif
    </div>
    <div class="flex flex-1 flex-col p-6 sm:p-7">
        <h3 class="font-serif text-2xl leading-snug sm:text-3xl">{{ $offer->title }}</h3>
        <p class="mt-3 flex-1 text-sm leading-relaxed text-muted">{{ $offer->description }}</p>
        @if ($offer->valid_until)
            <p class="mt-4 flex items-center gap-2 text-xs text-muted">
                <x-heroicon-o-calendar-days class="size-4 text-gold-500" />
                {{ __('site.offers.valid_until', ['date' => $offer->valid_until->translatedFormat('j F Y')]) }}
            </p>
        @endif
        <div class="mt-5 flex flex-wrap items-center gap-3 border-t border-line pt-5">
            @if ($offer->promo_code)
                <x-promo-code :code="$offer->promo_code" />
            @endif
            <a href="{{ route('booking', array_filter(['promo' => $offer->promo_code])) }}" class="btn-gold btn-sm ml-auto shrink-0">{{ __('site.offers.book_offer') }}</a>
        </div>
    </div>
</article>
