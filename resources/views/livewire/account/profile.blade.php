<div>
    @include('livewire.account.partials.header', ['heading' => __('account.profile.heading'), 'sub' => auth()->user()->email])

    <div class="container-x space-y-6 py-10 sm:py-12">
        {{-- Personal details --}}
        <section class="card grid gap-6 p-6 sm:p-8 lg:grid-cols-3">
            <div>
                <h2 class="font-serif text-2xl">{{ __('account.profile.details_title') }}</h2>
                <p class="mt-1 text-sm text-muted">{{ __('account.profile.details_text') }}</p>
            </div>
            <form wire:submit="saveProfile" class="grid gap-5 sm:grid-cols-2 lg:col-span-2" novalidate>
                <div class="sm:col-span-2">
                    <label for="name" class="label">{{ __('account.fields.name') }}</label>
                    <input id="name" type="text" wire:model="name" autocomplete="name" class="input @error('name') border-rose-400 @enderror">
                    @error('name') <p class="input-error">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="email_ro" class="label">{{ __('account.fields.email') }}</label>
                    <input id="email_ro" type="email" value="{{ auth()->user()->email }}" disabled class="input cursor-not-allowed opacity-70">
                </div>
                <div>
                    <label for="phone" class="label">{{ __('account.fields.phone') }}</label>
                    <input id="phone" type="tel" wire:model="phone" autocomplete="tel" class="input @error('phone') border-rose-400 @enderror">
                    @error('phone') <p class="input-error">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="country" class="label">{{ __('account.fields.country') }}</label>
                    <select id="country" wire:model="country" class="input @error('country') border-rose-400 @enderror">
                        <option value="">{{ __('booking.fields.country_placeholder') }}</option>
                        @foreach ($countries as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('country') <p class="input-error">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="locale" class="label">{{ __('account.fields.locale') }}</label>
                    <select id="locale" wire:model="locale" class="input">
                        @foreach (\App\Http\Middleware\SetLocale::LOCALES as $code => $label)
                            <option value="{{ $code }}">{{ $label }}</option>
                        @endforeach
                    </select>
                    <p class="mt-1 text-xs text-muted">{{ __('account.profile.locale_hint') }}</p>
                </div>
                <div class="sm:col-span-2">
                    <button type="submit" class="btn-gold" wire:loading.attr="disabled" wire:target="saveProfile">
                        <span wire:loading.remove wire:target="saveProfile">{{ __('account.profile.save') }}</span>
                        <span wire:loading wire:target="saveProfile">{{ __('booking.please_wait') }}</span>
                    </button>
                </div>
            </form>
        </section>

        {{-- Password --}}
        <section class="card grid gap-6 p-6 sm:p-8 lg:grid-cols-3">
            <div>
                <h2 class="font-serif text-2xl">{{ __('account.profile.password_title') }}</h2>
                <p class="mt-1 text-sm text-muted">{{ __('account.profile.password_text') }}</p>
            </div>
            <form wire:submit="updatePassword" class="grid gap-5 sm:grid-cols-2 lg:col-span-2" novalidate>
                <div class="sm:col-span-2 sm:max-w-sm">
                    <label for="current_password" class="label">{{ __('account.fields.current_password') }}</label>
                    <input id="current_password" type="password" wire:model="current_password" autocomplete="current-password" class="input @error('current_password') border-rose-400 @enderror">
                    @error('current_password') <p class="input-error">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="new_password" class="label">{{ __('account.fields.new_password') }}</label>
                    <input id="new_password" type="password" wire:model="new_password" autocomplete="new-password" class="input @error('new_password') border-rose-400 @enderror">
                    @error('new_password') <p class="input-error">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="new_password_confirmation" class="label">{{ __('account.fields.password_confirmation') }}</label>
                    <input id="new_password_confirmation" type="password" wire:model="new_password_confirmation" autocomplete="new-password" class="input">
                </div>
                <div class="sm:col-span-2">
                    <button type="submit" class="btn-dark" wire:loading.attr="disabled" wire:target="updatePassword">{{ __('account.profile.update_password') }}</button>
                </div>
            </form>
        </section>

        {{-- Danger zone --}}
        <section class="card grid gap-6 border-rose-200 p-6 sm:p-8 lg:grid-cols-3 dark:border-rose-900/60" x-data="{ confirmDelete: false }" @keydown.escape.window="confirmDelete = false">
            <div>
                <h2 class="font-serif text-2xl text-rose-700 dark:text-rose-300">{{ __('account.profile.delete_title') }}</h2>
                <p class="mt-1 text-sm text-muted">{{ __('account.profile.delete_text') }}</p>
            </div>
            <div class="lg:col-span-2">
                <button type="button" @click="confirmDelete = true" class="btn border border-rose-300 text-rose-700 hover:bg-rose-50 dark:border-rose-800 dark:text-rose-300 dark:hover:bg-rose-950/40">
                    @include('livewire.booking.partials.icon', ['name' => 'trash', 'class' => 'size-4']) {{ __('account.profile.delete_button') }}
                </button>
                @error('delete_password') <p class="input-error" x-show="!confirmDelete">{{ $message }}</p> @enderror
            </div>

            <template x-teleport="body">
                <div x-show="confirmDelete" x-cloak class="fixed inset-0 z-[70] flex items-end justify-center p-4 sm:items-center" role="dialog" aria-modal="true" aria-labelledby="delete-title">
                    <div x-show="confirmDelete" x-transition.opacity class="absolute inset-0 bg-midnight-950/70 backdrop-blur-sm" @click="confirmDelete = false"></div>
                    <form x-show="confirmDelete" x-transition x-trap.noscroll="confirmDelete" wire:submit="deleteAccount" class="card relative w-full max-w-md p-6 shadow-2xl">
                        <div class="grid size-12 place-items-center rounded-full bg-rose-100 text-rose-600 dark:bg-rose-950/60 dark:text-rose-300">@include('livewire.booking.partials.icon', ['name' => 'warning', 'class' => 'size-6'])</div>
                        <h3 id="delete-title" class="mt-4 font-serif text-2xl">{{ __('account.profile.delete_confirm_title') }}</h3>
                        <p class="mt-2 text-sm text-muted">{{ __('account.profile.delete_confirm_text') }}</p>
                        <label for="delete_password" class="label mt-4">{{ __('account.fields.password') }}</label>
                        <input id="delete_password" type="password" wire:model="delete_password" autocomplete="current-password" class="input @error('delete_password') border-rose-400 @enderror">
                        @error('delete_password') <p class="input-error">{{ $message }}</p> @enderror
                        <div class="mt-6 flex flex-col-reverse gap-2 sm:flex-row sm:justify-end">
                            <button type="button" class="btn-outline" @click="confirmDelete = false">{{ __('account.profile.keep') }}</button>
                            <button type="submit" class="btn bg-rose-600 text-white hover:bg-rose-700" wire:loading.attr="disabled" wire:target="deleteAccount">{{ __('account.profile.delete_confirm') }}</button>
                        </div>
                    </form>
                </div>
            </template>
        </section>
    </div>
</div>
