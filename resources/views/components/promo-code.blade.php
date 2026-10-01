@props(['code'])
<div x-data="{ copied: false, copy() { navigator.clipboard?.writeText(@js($code)).then(() => { this.copied = true; $dispatch('notify', { message: @js(__('site.offers.copied', ['code' => $code])) }); setTimeout(() => this.copied = false, 2000) }) } }"
     {{ $attributes->merge(['class' => 'inline-flex items-center overflow-hidden rounded-full border border-dashed border-gold-400 bg-gold-50 dark:bg-gold-900/30']) }}>
    <span class="px-4 py-1.5 font-mono text-sm font-bold tracking-widest text-gold-800 dark:text-gold-200">{{ $code }}</span>
    <button type="button" @click="copy()" class="flex items-center gap-1 border-l border-dashed border-gold-400 px-3 py-2 text-xs font-semibold text-gold-700 transition hover:bg-gold-100 dark:text-gold-200 dark:hover:bg-gold-900/60"
            :aria-label="copied ? @js(__('site.offers.copied_short')) : @js(__('site.offers.copy'))">
        <x-heroicon-o-clipboard-document x-show="!copied" class="size-4" />
        <x-heroicon-o-check x-show="copied" x-cloak class="size-4" />
        <span class="sr-only" x-text="copied ? @js(__('site.offers.copied_short')) : @js(__('site.offers.copy'))"></span>
    </button>
</div>
