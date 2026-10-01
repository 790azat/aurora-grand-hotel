<div>
    @if ($subscribed)
        <div class="flex items-start gap-3 rounded-2xl border border-gold-400/40 bg-white/5 p-4 text-sm" role="status">
            <x-heroicon-o-check-circle class="size-5 shrink-0 text-gold-400" />
            <div>
                <p class="font-semibold text-white">{{ __('site.newsletter.thanks') }}</p>
                <p class="mt-0.5 text-gray-400">{{ __('site.newsletter.success') }}</p>
            </div>
        </div>
    @else
        <form wire:submit="subscribe" novalidate>
            <label for="newsletter-email" class="sr-only">Email</label>
            <div class="flex overflow-hidden rounded-full border bg-white/5 transition focus-within:border-gold-400 {{ $errors->has('email') ? 'border-rose-400' : 'border-white/15' }}">
                <input id="newsletter-email" type="email" wire:model="email" placeholder="{{ __('site.newsletter.placeholder') }}" autocomplete="email"
                       class="min-w-0 flex-1 border-0 bg-transparent px-5 py-3 text-sm text-white placeholder:text-gray-500 focus:ring-0 focus:outline-none"
                       @error('email') aria-invalid="true" aria-describedby="newsletter-error" @enderror>
                <button type="submit" class="m-1 grid shrink-0 place-items-center rounded-full bg-gold-500 px-4 text-white transition hover:bg-gold-600 disabled:opacity-60" wire:loading.attr="disabled" aria-label="{{ __('site.newsletter.subscribe') }}">
                    <x-heroicon-o-arrow-right wire:loading.remove wire:target="subscribe" class="size-4" />
                    <span wire:loading wire:target="subscribe" class="size-4 animate-spin rounded-full border-2 border-white border-t-transparent"></span>
                </button>
            </div>
            @error('email')<p id="newsletter-error" class="mt-2 text-xs text-rose-400">{{ $message }}</p>@enderror
        </form>
    @endif
</div>
