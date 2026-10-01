<div>
    <x-page-hero :eyebrow="__('site.faq.eyebrow')" :title="__('site.faq.title')" :subtitle="__('site.faq.subtitle')" size="sm"
                 image="https://images.unsplash.com/photo-1540541338287-41700207dee6?auto=format&fit=crop&w=2000&q=80" />

    <section class="section pt-12">
        <div class="container-x max-w-3xl">
            <div class="relative">
                <x-heroicon-o-magnifying-glass class="pointer-events-none absolute top-1/2 left-5 size-5 -translate-y-1/2 text-muted" />
                <label for="faq-search" class="sr-only">{{ __('site.faq.search') }}</label>
                <input id="faq-search" type="search" wire:model.live.debounce.250ms="search" placeholder="{{ __('site.faq.search') }}" class="input rounded-full py-4 pr-12 pl-14 text-base shadow-sm">
                <span wire:loading.delay wire:target="search" class="absolute top-1/2 right-5 size-5 -translate-y-1/2 animate-spin rounded-full border-2 border-gold-400 border-t-transparent"></span>
            </div>
            @if ($search !== '')
                <p class="mt-4 text-center text-sm text-muted" aria-live="polite">{{ trans_choice('site.faq.found', $faqs->count(), ['count' => $faqs->count()]) }}</p>
            @endif

            <div class="mt-10 space-y-3" x-data="{ open: {{ $faqs->first()?->id ?? 'null' }} }">
                @forelse ($faqs as $faq)
                    <div wire:key="faq-{{ $faq->id }}" class="card overflow-hidden transition" :class="open === {{ $faq->id }} && 'border-gold-400/60 shadow-md'">
                        <h2>
                            <button type="button" @click="open = open === {{ $faq->id }} ? null : {{ $faq->id }}" :aria-expanded="open === {{ $faq->id }}" aria-controls="faq-a-{{ $faq->id }}"
                                    class="flex w-full items-center justify-between gap-4 px-6 py-5 text-left font-sans text-base font-semibold transition hover:text-gold-600 dark:hover:text-gold-300">
                                <span>{{ $faq->question }}</span>
                                <span class="grid size-8 shrink-0 place-items-center rounded-full border border-line transition" :class="open === {{ $faq->id }} && 'rotate-45 border-gold-400 bg-gold-400 text-white'">
                                    <x-heroicon-o-plus class="size-4" />
                                </span>
                            </button>
                        </h2>
                        <div id="faq-a-{{ $faq->id }}" x-show="open === {{ $faq->id }}" x-collapse x-cloak>
                            <div class="px-6 pb-6 text-sm leading-relaxed text-muted">{!! nl2br(e($faq->answer)) !!}</div>
                        </div>
                    </div>
                @empty
                    <x-empty-state icon="question-mark-circle" :title="__('site.faq.empty_title')" :text="__('site.faq.empty_text')">
                        <button type="button" wire:click="$set('search', '')" class="btn-outline mt-6">{{ __('site.faq.clear') }}</button>
                    </x-empty-state>
                @endforelse
            </div>

            <div class="mt-14 flex flex-col items-center gap-4 rounded-3xl bg-midnight-900 p-8 text-center text-white sm:p-10">
                <x-heroicon-o-chat-bubble-left-right class="size-10 text-gold-300" />
                <h2 class="font-serif text-3xl">{{ __('site.faq.still_title') }}</h2>
                <p class="max-w-md text-sm text-white/70">{{ __('site.faq.still_text') }}</p>
                <div class="mt-2 flex flex-wrap justify-center gap-3">
                    <a href="{{ route('contact') }}" wire:navigate class="btn-gold">{{ __('site.faq.contact_us') }}</a>
                    <a href="tel:{{ preg_replace('/[^\d+]/', '', setting('hotel_phone')) }}" class="btn-ghost-light"><x-heroicon-o-phone class="size-4" /> {{ setting('hotel_phone') }}</a>
                </div>
            </div>
        </div>
    </section>
</div>
