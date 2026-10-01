<div>
    @component('livewire.auth.partials.shell', ['title' => __('account.auth.register_heading'), 'subtitle' => __('account.auth.register_text')])
        <form wire:submit="register" class="space-y-5" novalidate>
            <div>
                <label for="name" class="label">{{ __('account.fields.name') }}</label>
                <input id="name" type="text" wire:model.blur="name" autocomplete="name" autofocus class="input @error('name') border-rose-400 @enderror">
                @error('name') <p class="input-error">{{ $message }}</p> @enderror
            </div>
            <div class="grid gap-5 sm:grid-cols-2">
                <div>
                    <label for="email" class="label">{{ __('account.fields.email') }}</label>
                    <input id="email" type="email" wire:model.blur="email" autocomplete="email" class="input @error('email') border-rose-400 @enderror">
                    @error('email') <p class="input-error">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="phone" class="label">{{ __('account.fields.phone') }} <span class="font-normal normal-case tracking-normal">({{ __('account.optional') }})</span></label>
                    <input id="phone" type="tel" wire:model.blur="phone" autocomplete="tel" class="input @error('phone') border-rose-400 @enderror">
                    @error('phone') <p class="input-error">{{ $message }}</p> @enderror
                </div>
            </div>
            <div>
                <label for="password" class="label">{{ __('account.fields.password') }}</label>
                <input id="password" type="password" wire:model.blur="password" autocomplete="new-password" class="input @error('password') border-rose-400 @enderror">
                <p class="mt-1 text-xs text-muted">{{ __('booking.account.password_hint') }}</p>
                @error('password') <p class="input-error">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="password_confirmation" class="label">{{ __('account.fields.password_confirmation') }}</label>
                <input id="password_confirmation" type="password" wire:model="password_confirmation" autocomplete="new-password" class="input">
            </div>
            <p class="text-xs text-muted">{!! __('account.auth.register_terms', ['terms' => '<a href="'.route('terms').'" class="link" target="_blank">'.e(__('booking.terms_inline')).'</a>', 'privacy' => '<a href="'.route('privacy').'" class="link" target="_blank">'.e(__('booking.privacy_inline')).'</a>']) !!}</p>
            <button type="submit" class="btn-gold w-full" wire:loading.attr="disabled" wire:target="register">
                <span wire:loading.remove wire:target="register">{{ __('account.auth.register_button') }}</span>
                <span wire:loading wire:target="register">{{ __('booking.please_wait') }}</span>
            </button>
        </form>
        <p class="mt-8 text-center text-sm text-muted">
            {{ __('account.auth.have_account') }}
            <a href="{{ route('login') }}" wire:navigate class="link font-semibold">{{ __('account.auth.login_link') }}</a>
        </p>
    @endcomponent
</div>
