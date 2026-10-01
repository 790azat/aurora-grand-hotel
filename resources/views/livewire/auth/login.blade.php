<div>
    @component('livewire.auth.partials.shell', ['title' => __('account.auth.login_heading'), 'subtitle' => __('account.auth.login_text')])
        {{-- Demo hint --}}
        <div class="mb-6 flex items-start gap-3 rounded-2xl border border-gold-300 bg-gold-50 p-4 text-sm text-gold-900 dark:border-gold-700 dark:bg-gold-900/25 dark:text-gold-100">
            @include('livewire.booking.partials.icon', ['name' => 'sparkles', 'class' => 'size-5 shrink-0'])
            <div class="min-w-0 flex-1">
                <p class="font-semibold">{{ __('account.auth.demo_title') }}</p>
                <p class="mt-0.5 text-xs">{!! __('account.auth.demo_text', ['email' => '<code class="font-mono">guest@demo.com</code>', 'password' => '<code class="font-mono">password</code>']) !!}</p>
                <p class="mt-1 text-xs opacity-80">{{ __('account.auth.demo_staff') }}</p>
            </div>
            <button type="button" wire:click="fillDemo" class="btn-dark btn-sm shrink-0">{{ __('account.auth.fill') }}</button>
        </div>

        <form wire:submit="login" class="space-y-5" novalidate>
            <div>
                <label for="email" class="label">{{ __('account.fields.email') }}</label>
                <input id="email" type="email" wire:model="email" autocomplete="username" autofocus class="input @error('email') border-rose-400 @enderror">
                @error('email') <p class="input-error">{{ $message }}</p> @enderror
            </div>
            <div x-data="{ show: false }">
                <div class="flex items-center justify-between">
                    <label for="password" class="label">{{ __('account.fields.password') }}</label>
                    <a href="{{ route('password.request') }}" wire:navigate class="mb-1.5 text-xs font-semibold text-gold-600 hover:underline dark:text-gold-300">{{ __('account.auth.forgot_link') }}</a>
                </div>
                <div class="relative">
                    <input id="password" :type="show ? 'text' : 'password'" wire:model="password" autocomplete="current-password" class="input pr-12 @error('password') border-rose-400 @enderror">
                    <button type="button" @click="show = !show" class="absolute inset-y-0 right-0 grid w-11 place-items-center text-muted hover:text-ink" :aria-label="show ? @js(__('booking.account.hide_password')) : @js(__('booking.account.show_password'))">
                        @include('livewire.booking.partials.icon', ['name' => 'eye', 'class' => 'size-5'])
                    </button>
                </div>
                @error('password') <p class="input-error">{{ $message }}</p> @enderror
            </div>
            <label class="flex cursor-pointer items-center gap-2 text-sm">
                <input type="checkbox" wire:model="remember" class="size-4 rounded border-line accent-gold-500">
                {{ __('account.auth.remember') }}
            </label>
            <button type="submit" class="btn-gold w-full" wire:loading.attr="disabled" wire:target="login">
                <span wire:loading.remove wire:target="login">{{ __('account.auth.login_button') }}</span>
                <span wire:loading wire:target="login">{{ __('booking.please_wait') }}</span>
            </button>
        </form>

        <p class="mt-8 text-center text-sm text-muted">
            {{ __('account.auth.no_account') }}
            <a href="{{ route('register') }}" wire:navigate class="link font-semibold">{{ __('account.auth.register_link') }}</a>
        </p>
        <p class="mt-2 text-center text-sm text-muted">
            <a href="{{ route('booking.lookup') }}" wire:navigate class="hover:text-ink">{{ __('account.auth.lookup_link') }}</a>
        </p>
    @endcomponent
</div>
