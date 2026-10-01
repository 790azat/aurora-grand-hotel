<div>
    <x-page-hero :eyebrow="__('site.offers.eyebrow')" :title="__('site.offers.title')" :subtitle="__('site.offers.subtitle')"
                 image="https://images.unsplash.com/photo-1571896349842-33c89424de2d?auto=format&fit=crop&w=2000&q=80" />

    <section class="section">
        <div class="container-x">
            @if ($offers->isNotEmpty())
                <div class="grid gap-8 md:grid-cols-2">
                    @foreach ($offers as $offer)
                        <x-offer-card :offer="$offer" wire:key="offer-{{ $offer->id }}" />
                    @endforeach
                </div>
            @else
                <x-empty-state icon="gift" :title="__('site.offers.empty_title')" :text="__('site.offers.empty_text')">
                    <a href="{{ route('rooms.index') }}" wire:navigate class="btn-gold mt-6">{{ __('site.home.explore_rooms') }}</a>
                </x-empty-state>
            @endif

            <div class="mt-16 grid gap-6 rounded-3xl border border-line bg-surface p-6 sm:p-10 lg:grid-cols-[1fr_2fr] lg:items-center">
                <div>
                    <p class="eyebrow">{{ __('site.offers.how_eyebrow') }}</p>
                    <h2 class="mt-3 font-serif text-3xl">{{ __('site.offers.how_title') }}</h2>
                </div>
                <ol class="grid gap-6 sm:grid-cols-3">
                    @foreach (__('site.offers.how_steps') as $n => $step)
                        <li>
                            <span class="grid size-10 place-items-center rounded-full bg-gold-100 font-serif text-lg font-semibold text-gold-800 dark:bg-gold-900/60 dark:text-gold-200">{{ $n + 1 }}</span>
                            <p class="mt-3 text-sm leading-relaxed text-muted">{{ $step }}</p>
                        </li>
                    @endforeach
                </ol>
            </div>
            <p class="mt-6 text-center text-xs text-muted">{{ __('site.offers.terms_note') }}</p>
        </div>
    </section>
</div>
