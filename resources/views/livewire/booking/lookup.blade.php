<div>
    <section class="relative overflow-hidden bg-midnight-900 text-white">
        <div class="pointer-events-none absolute inset-0 opacity-40" style="background: radial-gradient(60% 120% at 85% 0%, rgba(203,166,90,.35), transparent 60%);"></div>
        <div class="container-x relative py-12 sm:py-16">
            <p class="eyebrow text-gold-300">{{ __('booking.lookup.eyebrow') }}</p>
            <h1 class="mt-2 font-serif text-4xl sm:text-5xl">{{ __('booking.lookup.title') }}</h1>
            <p class="mt-3 max-w-2xl text-sm text-gray-300 sm:text-base">{{ __('booking.lookup.text') }}</p>
        </div>
    </section>

    <div class="container-x py-10 sm:py-14">
        @if ($this->booking)
            @php $b = $this->booking; @endphp
            <div class="flex flex-wrap items-center justify-between gap-4">
                <div>
                    <p class="text-xs font-semibold tracking-[0.25em] text-muted uppercase">{{ __('booking.reference') }}</p>
                    <p class="mt-1 flex flex-wrap items-center gap-3">
                        <span class="font-mono text-2xl font-semibold tracking-[0.12em] text-gold-600 dark:text-gold-300">{{ $b->reference }}</span>
                        @include('livewire.booking.partials.status-badge', ['booking' => $b])
                    </p>
                </div>
                <button type="button" wire:click="close" class="btn-outline btn-sm">@include('livewire.booking.partials.icon', ['name' => 'search', 'class' => 'size-4']) {{ __('booking.lookup.another') }}</button>
            </div>

            <div class="mt-6">
                @include('livewire.booking.partials.manage-actions', ['booking' => $b])
            </div>

            <div class="mt-8">
                @include('livewire.booking.partials.details', ['booking' => $b])
            </div>
        @else
            <div class="grid gap-8 lg:grid-cols-5">
                <div class="card p-6 sm:p-8 lg:col-span-3">
                    <h2 class="font-serif text-3xl">{{ __('booking.lookup.form_title') }}</h2>
                    <p class="mt-1 text-sm text-muted">{{ __('booking.lookup.form_text') }}</p>
                    <form wire:submit="find" class="mt-6 space-y-5" novalidate>
                        <div>
                            <label for="reference" class="label">{{ __('booking.reference') }}</label>
                            <input id="reference" type="text" wire:model="reference" placeholder="AGH-XXXXXX" autocomplete="off" class="input font-mono uppercase tracking-wider @error('reference') border-rose-400 @enderror">
                            @error('reference') <p class="input-error">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="lookup_email" class="label">{{ __('booking.fields.email') }}</label>
                            <input id="lookup_email" type="email" wire:model="email" autocomplete="email" placeholder="you@example.com" class="input @error('email') border-rose-400 @enderror">
                            @error('email') <p class="input-error">{{ $message }}</p> @enderror
                        </div>
                        <button type="submit" class="btn-gold w-full sm:w-auto" wire:loading.attr="disabled" wire:target="find">
                            <span wire:loading.remove wire:target="find" class="flex items-center gap-2">@include('livewire.booking.partials.icon', ['name' => 'search', 'class' => 'size-4']) {{ __('booking.lookup.submit') }}</span>
                            <span wire:loading wire:target="find">{{ __('booking.please_wait') }}</span>
                        </button>
                    </form>
                </div>

                <div class="space-y-6 lg:col-span-2">
                    @if ($this->recent->isNotEmpty())
                        <div class="card p-6">
                            <h3 class="font-sans text-base font-semibold">{{ __('booking.lookup.recent') }}</h3>
                            <ul class="mt-4 divide-y divide-line">
                                @foreach ($this->recent as $r)
                                    <li wire:key="recent-{{ $r->id }}">
                                        <button type="button" wire:click="open('{{ $r->reference }}')" class="flex w-full items-center justify-between gap-3 py-3 text-left text-sm hover:text-gold-600 dark:hover:text-gold-300">
                                            <span>
                                                <span class="block font-mono font-semibold tracking-wider">{{ $r->reference }}</span>
                                                <span class="block text-xs text-muted">{{ $r->roomType->name }} · {{ $r->check_in->translatedFormat('j M') }} — {{ $r->check_out->translatedFormat('j M Y') }}</span>
                                            </span>
                                            @include('livewire.booking.partials.icon', ['name' => 'chevron-right', 'class' => 'size-4 shrink-0'])
                                        </button>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    <div class="card p-6">
                        <span class="grid size-11 place-items-center rounded-full bg-elevated text-gold-600 dark:text-gold-300">@include('livewire.booking.partials.icon', ['name' => 'user', 'class' => 'size-5'])</span>
                        <h3 class="mt-4 font-sans text-base font-semibold">{{ __('booking.lookup.account_title') }}</h3>
                        <p class="mt-1 text-sm text-muted">{{ __('booking.lookup.account_text') }}</p>
                        <a href="{{ auth()->check() ? route('account.dashboard') : route('login') }}" class="btn-outline btn-sm mt-4">{{ auth()->check() ? __('booking.go_to_account') : __('booking.lookup.sign_in') }}</a>
                    </div>
                    <div class="rounded-2xl border border-line p-6 text-sm text-muted">
                        <p class="font-semibold text-ink">{{ __('booking.lookup.help_title') }}</p>
                        <p class="mt-1">{{ __('booking.lookup.help_text') }}</p>
                        <p class="mt-3 flex flex-col gap-1">
                            <a href="tel:{{ preg_replace('/[^\d+]/', '', setting('hotel_phone')) }}" class="link">{{ setting('hotel_phone') }}</a>
                            <a href="mailto:{{ setting('hotel_email') }}" class="link break-all">{{ setting('hotel_email') }}</a>
                        </p>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>
