<div>
    @component('livewire.auth.partials.shell', ['title' => __('account.auth.forgot_heading'), 'subtitle' => __('account.auth.forgot_text')])
        @if ($sent)
            <div class="card p-6 text-center">
                <div class="mx-auto grid size-14 place-items-center rounded-full bg-gold-100 text-gold-700 dark:bg-gold-900/50 dark:text-gold-200">
                    @include('livewire.booking.partials.icon', ['name' => 'mail', 'class' => 'size-7'])
                </div>
                <h2 class="mt-4 font-serif text-2xl">{{ __('account.auth.forgot_sent_title') }}</h2>
                <p class="mt-2 text-sm text-muted">{{ __('account.auth.forgot_sent_text', ['email' => $email]) }}</p>
                <div class="mt-4 rounded-xl bg-elevated p-3 text-xs text-muted">{{ __('account.auth.forgot_demo') }}</div>
                <a href="{{ route('login') }}" wire:navigate class="btn-gold mt-6 w-full">{{ __('account.auth.back_to_login') }}</a>
            </div>
        @else
            <form wire:submit="send" class="space-y-5" novalidate>
                <div>
                    <label for="email" class="label">{{ __('account.fields.email') }}</label>
                    <input id="email" type="email" wire:model="email" autocomplete="email" autofocus class="input @error('email') border-rose-400 @enderror">
                    @error('email') <p class="input-error">{{ $message }}</p> @enderror
                </div>
                <button type="submit" class="btn-gold w-full" wire:loading.attr="disabled" wire:target="send">
                    <span wire:loading.remove wire:target="send">{{ __('account.auth.forgot_button') }}</span>
                    <span wire:loading wire:target="send">{{ __('booking.please_wait') }}</span>
                </button>
            </form>
            <p class="mt-8 text-center text-sm text-muted">
                <a href="{{ route('login') }}" wire:navigate class="link font-semibold">{{ __('account.auth.back_to_login') }}</a>
            </p>
        @endif
    @endcomponent
</div>
