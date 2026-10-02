@php
    $steps = [1 => __('booking.steps.dates'), 2 => __('booking.steps.extras'), 3 => __('booking.steps.details'), 4 => __('booking.steps.payment')];
    $quote = $this->quote;
    $type = $selected['type'] ?? null;
    $fmt = fn ($d, $f = 'D, j M Y') => \Illuminate\Support\Carbon::parse($d)->translatedFormat($f);
    $guestsLabel = trans_choice('booking.adults_count', (int) $adults).((int) $children ? ', '.trans_choice('booking.children_count', (int) $children) : '');
    $pricingLabels = ['per_stay' => __('booking.pricing.per_stay'), 'per_night' => __('booking.pricing.per_night'), 'per_guest_night' => __('booking.pricing.per_guest_night')];
@endphp

<div x-data x-on:booking-step.window="window.scrollTo({ top: 0, behavior: 'smooth' })">
    {{-- Page header --}}
    <section class="relative overflow-hidden bg-midnight-900 text-white">
        <div class="pointer-events-none absolute inset-0 opacity-40" style="background: radial-gradient(60% 120% at 85% 0%, rgba(203,166,90,.35), transparent 60%), radial-gradient(40% 80% at 0% 100%, rgba(203,166,90,.15), transparent 60%);"></div>
        <div class="container-x relative py-10 sm:py-14">
            <p class="eyebrow text-gold-300">{{ __('booking.eyebrow') }}</p>
            <h1 class="mt-2 font-serif text-4xl sm:text-5xl">{{ __('booking.heading') }}</h1>
            <p class="mt-3 max-w-2xl text-sm text-gray-300 sm:text-base">{{ __('booking.subheading') }}</p>

            {{-- Stepper --}}
            <ol class="mt-8 grid grid-cols-4 gap-2 sm:gap-4" aria-label="{{ __('booking.progress') }}">
                @foreach ($steps as $n => $label)
                    @php $done = $n < $step; $current = $n === $step; $reachable = $n < $step || ($n <= 3 && $selected && $step >= $n - 1) ; @endphp
                    <li>
                        <button type="button" @if ($done) wire:click="goToStep({{ $n }})" @else disabled @endif
                                class="group flex w-full flex-col gap-2 text-left {{ $done ? 'cursor-pointer' : 'cursor-default' }}"
                                @if ($current) aria-current="step" @endif>
                            <span class="h-1 w-full rounded-full transition-colors {{ $n <= $step ? 'bg-gold-400' : 'bg-white/15' }}"></span>
                            <span class="flex items-center gap-2">
                                <span class="grid size-6 shrink-0 place-items-center rounded-full text-[11px] font-bold transition
                                    {{ $done ? 'bg-gold-400 text-midnight-900 group-hover:bg-gold-300' : ($current ? 'bg-white text-midnight-900' : 'border border-white/25 text-white/60') }}">
                                    @if ($done)
                                        @include('livewire.booking.partials.icon', ['name' => 'check', 'class' => 'size-3.5', 'stroke' => 3])
                                    @else
                                        {{ $n }}
                                    @endif
                                </span>
                                <span class="hidden text-xs font-semibold tracking-wide sm:inline {{ $current ? 'text-white' : ($done ? 'text-gold-200 group-hover:text-white' : 'text-white/50') }}">{{ $label }}</span>
                            </span>
                        </button>
                    </li>
                @endforeach
            </ol>
            <p class="mt-3 text-xs font-semibold text-gold-200 sm:hidden">{{ __('booking.step_of', ['n' => $step, 'total' => 4]) }} · {{ $steps[$step] }}</p>
        </div>
    </section>

    {{-- Mobile summary bar --}}
    <div x-data="{ open: false }" class="sticky top-18 z-30 border-b border-line bg-surface/95 shadow-sm backdrop-blur lg:hidden">
        <button type="button" @click="open = !open" class="container-x flex w-full items-center justify-between gap-3 py-3 text-left" :aria-expanded="open">
            <span class="min-w-0">
                <span class="block truncate text-xs text-muted">
                    {{ $type ? $type->name.' · ' : '' }}{{ $this->nights ? trans_choice('booking.nights_count', $this->nights) : __('booking.select_dates') }}
                </span>
                <span class="block font-serif text-xl font-semibold">
                    {{ $quote ? money($quote['total'], true) : __('booking.summary.choose_room') }}
                </span>
            </span>
            <span class="flex items-center gap-1 text-xs font-semibold text-gold-600 dark:text-gold-300">
                <span x-text="open ? @js(__('booking.summary.hide')) : @js(__('booking.summary.show'))"></span>
                @include('livewire.booking.partials.icon', ['name' => 'chevron-down', 'class' => 'size-4 transition', 'attrs' => ':class="open && \'rotate-180\'"'])
            </span>
        </button>
        <div x-show="open" x-collapse x-cloak class="container-x max-h-[70vh] overflow-y-auto pb-4">
            @include('livewire.booking.partials.summary', ['mobile' => true])
        </div>
    </div>

    <div class="container-x grid gap-8 py-8 sm:py-12 lg:grid-cols-12 lg:gap-10">
        <div class="min-w-0 lg:col-span-8">
            @if ($error)
                <div class="mb-6 flex items-start gap-3 rounded-2xl border border-rose-300 bg-rose-50 p-4 text-sm text-rose-800 dark:border-rose-800 dark:bg-rose-950/40 dark:text-rose-200" role="alert">
                    @include('livewire.booking.partials.icon', ['name' => 'warning', 'class' => 'size-5 shrink-0'])
                    <p>{{ $error }}</p>
                </div>
            @endif

            {{-- ============ STEP 1: dates & room ============ --}}
            @if ($step === 1)
                <div class="card p-5 sm:p-6">
                    <div class="grid grid-cols-2 gap-3 sm:gap-4 xl:grid-cols-4">
                        <div>
                            <label for="check_in" class="label">{{ __('booking.check_in') }}</label>
                            <input id="check_in" type="date" wire:model.live="check_in" min="{{ today()->toDateString() }}" class="input" required>
                        </div>
                        <div>
                            <label for="check_out" class="label">{{ __('booking.check_out') }}</label>
                            <input id="check_out" type="date" wire:model.live="check_out" min="{{ \Illuminate\Support\Carbon::parse($check_in ?: today())->addDay()->toDateString() }}" class="input" required>
                        </div>
                        @foreach (['adults' => [1, \App\Livewire\Booking\Wizard::MAX_ADULTS, __('booking.adults'), __('booking.adults_hint')], 'children' => [0, \App\Livewire\Booking\Wizard::MAX_CHILDREN, __('booking.children'), __('booking.children_hint')]] as $field => [$min, $max, $label, $hint])
                            <div>
                                <span class="label" id="{{ $field }}-label">{{ $label }} <span class="font-normal normal-case tracking-normal text-muted/80">· {{ $hint }}</span></span>
                                <div class="flex items-center justify-between rounded-xl border border-line bg-surface px-2 py-1.5" role="group" aria-labelledby="{{ $field }}-label">
                                    <button type="button" wire:click="changeGuests('{{ $field }}', -1)" @disabled($$field <= $min)
                                            class="grid size-9 place-items-center rounded-lg text-ink transition hover:bg-elevated disabled:opacity-30" aria-label="{{ __('booking.decrease') }}">
                                        @include('livewire.booking.partials.icon', ['name' => 'minus', 'class' => 'size-4', 'stroke' => 2])
                                    </button>
                                    <span class="min-w-8 text-center text-base font-semibold" aria-live="polite">{{ $$field }}</span>
                                    <button type="button" wire:click="changeGuests('{{ $field }}', 1)" @disabled($$field >= $max)
                                            class="grid size-9 place-items-center rounded-lg text-ink transition hover:bg-elevated disabled:opacity-30" aria-label="{{ __('booking.increase') }}">
                                        @include('livewire.booking.partials.icon', ['name' => 'plus', 'class' => 'size-4', 'stroke' => 2])
                                    </button>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    <div class="mt-4 flex flex-wrap items-center gap-x-4 gap-y-2 border-t border-line pt-4 text-sm text-muted">
                        @if ($this->dateError)
                            <span class="flex items-center gap-2 font-medium text-rose-600 dark:text-rose-400">
                                @include('livewire.booking.partials.icon', ['name' => 'warning', 'class' => 'size-4'])
                                {{ $this->dateError }}
                            </span>
                        @else
                            <span class="flex items-center gap-2">
                                @include('livewire.booking.partials.icon', ['name' => 'moon', 'class' => 'size-4 text-gold-500'])
                                <strong class="text-ink">{{ trans_choice('booking.nights_count', $this->nights) }}</strong>
                                · {{ $fmt($check_in, 'j M') }} — {{ $fmt($check_out, 'j M Y') }}
                            </span>
                            <span class="flex items-center gap-2">
                                @include('livewire.booking.partials.icon', ['name' => 'users', 'class' => 'size-4 text-gold-500'])
                                {{ $guestsLabel }}
                            </span>
                        @endif
                        <span wire:loading.flex wire:target="check_in,check_out,changeGuests,extendStay" class="ml-auto items-center gap-2 text-xs">
                            <svg class="size-4 animate-spin text-gold-500" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="9" stroke="currentColor" stroke-opacity=".25" stroke-width="3"/><path d="M21 12a9 9 0 0 0-9-9" stroke="currentColor" stroke-width="3" stroke-linecap="round"/></svg>
                            {{ __('booking.checking') }}
                        </span>
                    </div>
                </div>

                <div class="mt-8 flex items-end justify-between gap-4">
                    <div>
                        <h2 class="font-serif text-3xl">{{ __('booking.available_rooms') }}</h2>
                        @unless ($this->dateError)
                            <p class="mt-1 text-sm text-muted">{{ __('booking.prices_note') }}</p>
                        @endunless
                    </div>
                </div>

                <div class="mt-5 space-y-5 transition-opacity" wire:loading.class="opacity-50 pointer-events-none" wire:target="check_in,check_out,changeGuests,extendStay">
                    @forelse ($this->results as $r)
                        @php
                            $t = $r['type']; $q = $r['quote'];
                            $soldOut = $r['available'] < 1;
                            $tooShort = ! $soldOut && $q['nights'] < $q['min_nights'];
                            $isSelected = $room_type === $t->slug && ! $soldOut && ! $tooShort;
                        @endphp
                        <article wire:key="room-{{ $t->id }}" class="card group overflow-hidden transition {{ $isSelected ? 'ring-2 ring-gold-400' : 'hover:shadow-lg' }} {{ $soldOut ? 'opacity-80' : '' }}">
                            <div class="grid md:grid-cols-5">
                                <div class="relative aspect-[16/10] overflow-hidden md:col-span-2 md:aspect-auto md:min-h-60">
                                    <img src="{{ $t->cover }}" alt="{{ $t->name }}" loading="lazy" class="absolute inset-0 size-full object-cover transition duration-700 group-hover:scale-105 {{ $soldOut ? 'grayscale' : '' }}">
                                    <div class="absolute top-3 left-3 flex flex-wrap gap-2">
                                        @if ($soldOut)
                                            <span class="badge bg-midnight-900/90 text-white">{{ __('booking.sold_out') }}</span>
                                        @elseif ($r['available'] <= 3)
                                            <span class="badge bg-rose-600 text-white shadow">{{ trans_choice('booking.only_left', $r['available']) }}</span>
                                        @endif
                                        @if ($isSelected)
                                            <span class="badge bg-gold-400 text-midnight-900">@include('livewire.booking.partials.icon', ['name' => 'check', 'class' => 'size-3', 'stroke' => 3]) {{ __('booking.selected') }}</span>
                                        @endif
                                    </div>
                                </div>
                                <div class="flex flex-col p-5 sm:p-6 md:col-span-3">
                                    <div class="flex flex-wrap items-start justify-between gap-3">
                                        <div class="min-w-0">
                                            <h3 class="font-serif text-2xl leading-tight sm:text-[1.7rem]">{{ $t->name }}</h3>
                                            <p class="mt-1 text-sm text-muted">{{ $t->short }}</p>
                                        </div>
                                        <a href="{{ route('rooms.show', $t) }}" target="_blank" class="link shrink-0 text-xs font-semibold">{{ __('booking.room_details') }}</a>
                                    </div>
                                    <ul class="mt-4 grid grid-cols-2 gap-x-4 gap-y-2 text-xs text-muted">
                                        <li class="flex items-center gap-1.5">@include('livewire.booking.partials.icon', ['name' => 'size', 'class' => 'size-4 text-gold-500']) {{ $t->size_m2 }} m²</li>
                                        <li class="flex items-center gap-1.5">@include('livewire.booking.partials.icon', ['name' => 'users', 'class' => 'size-4 text-gold-500']) {{ __('booking.up_to_guests', ['n' => $t->max_guests]) }}</li>
                                        <li class="flex min-w-0 items-center gap-1.5">@include('livewire.booking.partials.icon', ['name' => 'bed', 'class' => 'size-4 shrink-0 text-gold-500']) <span class="truncate" title="{{ $t->beds }}">{{ $t->beds }}</span></li>
                                        <li class="flex min-w-0 items-center gap-1.5">@include('livewire.booking.partials.icon', ['name' => 'eye', 'class' => 'size-4 shrink-0 text-gold-500']) <span class="truncate" title="{{ $t->view }}">{{ $t->view }}</span></li>
                                    </ul>
                                    @if ($t->amenities->isNotEmpty())
                                        <div class="mt-3 flex flex-wrap gap-1.5">
                                            @foreach ($t->amenities->take(4) as $amenity)
                                                <span class="rounded-full bg-elevated px-2.5 py-1 text-[11px] text-muted">{{ $amenity->name }}</span>
                                            @endforeach
                                            @if ($t->amenities->count() > 4)
                                                <span class="rounded-full px-1 py-1 text-[11px] text-muted">+{{ $t->amenities->count() - 4 }}</span>
                                            @endif
                                        </div>
                                    @endif

                                    @if ($tooShort)
                                        <div class="mt-4 flex flex-wrap items-center justify-between gap-3 rounded-xl bg-gold-50 px-4 py-3 text-sm text-gold-800 dark:bg-gold-900/30 dark:text-gold-200">
                                            <span class="flex items-center gap-2">@include('livewire.booking.partials.icon', ['name' => 'info', 'class' => 'size-4 shrink-0']) {{ trans_choice('booking.min_nights_notice', $q['min_nights']) }}</span>
                                            <button type="button" wire:click="extendStay({{ $q['min_nights'] }})" class="font-semibold underline underline-offset-4">{{ trans_choice('booking.extend_to', $q['min_nights']) }}</button>
                                        </div>
                                    @endif

                                    <div class="mt-auto flex flex-wrap items-end justify-between gap-4 pt-5">
                                        <div>
                                            <p class="text-xs text-muted">{{ __('booking.avg_per_night') }}</p>
                                            <p class="font-serif text-3xl font-semibold text-ink">{{ money($q['avg_nightly']) }}</p>
                                            <p class="text-xs text-muted">{{ __('booking.total_for', ['nights' => trans_choice('booking.nights_count', $q['nights']), 'amount' => money($q['room_total'])]) }} · {{ __('booking.plus_tax') }}</p>
                                        </div>
                                        @if ($soldOut)
                                            <span class="btn-outline pointer-events-none opacity-60">{{ __('booking.sold_out_try') }}</span>
                                        @elseif ($tooShort)
                                            <button type="button" disabled class="btn-outline">{{ __('booking.select_room') }}</button>
                                        @else
                                            <button type="button" wire:click="selectRoom('{{ $t->slug }}')" class="{{ $isSelected ? 'btn-gold' : 'btn-dark' }}">
                                                <span wire:loading.remove wire:target="selectRoom('{{ $t->slug }}')">{{ $isSelected ? __('booking.continue_with_room') : __('booking.select_room') }}</span>
                                                <span wire:loading wire:target="selectRoom('{{ $t->slug }}')">{{ __('booking.please_wait') }}</span>
                                                @include('livewire.booking.partials.icon', ['name' => 'arrow-right', 'class' => 'size-4'])
                                            </button>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </article>
                    @empty
                        <div class="card flex flex-col items-center px-6 py-14 text-center">
                            <span class="grid size-14 place-items-center rounded-full bg-elevated text-gold-500">@include('livewire.booking.partials.icon', ['name' => $this->dateError ? 'calendar' : 'users', 'class' => 'size-7'])</span>
                            <h3 class="mt-4 font-serif text-2xl">{{ $this->dateError ? __('booking.empty.dates_title') : __('booking.empty.party_title') }}</h3>
                            <p class="mt-2 max-w-md text-sm text-muted">{{ $this->dateError ? __('booking.empty.dates_text') : __('booking.empty.party_text', ['phone' => setting('hotel_phone')]) }}</p>
                        </div>
                    @endforelse
                </div>
            @endif

            {{-- ============ STEP 2: extras ============ --}}
            @if ($step === 2 && $selected)
                <div>
                    <h2 class="font-serif text-3xl">{{ __('booking.extras_title') }}</h2>
                    <p class="mt-1 text-sm text-muted">{{ __('booking.extras_text') }}</p>
                </div>
                <div class="mt-6 grid gap-4 sm:grid-cols-2">
                    @foreach ($this->extraOptions as $extra)
                        @php
                            $on = ! empty($extras[$extra->id]);
                            $qty = $extra->quantityFor(max($this->nights, 1), (int) $adults + (int) $children);
                            $line = $qty * (float) $extra->price;
                        @endphp
                        <button type="button" wire:key="extra-{{ $extra->id }}" wire:click="toggleExtra({{ $extra->id }})" aria-pressed="{{ $on ? 'true' : 'false' }}"
                                class="card group relative flex gap-4 p-5 text-left transition hover:-translate-y-0.5 hover:shadow-lg {{ $on ? 'border-gold-400 ring-2 ring-gold-400/70 bg-gold-50/60 dark:bg-gold-900/15' : '' }}">
                            <span class="grid size-12 shrink-0 place-items-center rounded-xl transition {{ $on ? 'bg-gold-400 text-midnight-900' : 'bg-elevated text-gold-600 dark:text-gold-300' }}">
                                @include('livewire.booking.partials.icon', ['name' => $extra->icon, 'class' => 'size-6'])
                            </span>
                            <span class="min-w-0 flex-1">
                                <span class="flex items-start justify-between gap-2">
                                    <span class="font-semibold text-ink">{{ $extra->name }}</span>
                                    <span class="grid size-6 shrink-0 place-items-center rounded-full border transition {{ $on ? 'border-gold-500 bg-gold-500 text-white' : 'border-line text-transparent group-hover:border-gold-400' }}">
                                        @include('livewire.booking.partials.icon', ['name' => 'check', 'class' => 'size-3.5', 'stroke' => 3])
                                    </span>
                                </span>
                                <span class="mt-1 block text-xs text-muted">{{ $extra->description }}</span>
                                <span class="mt-3 flex flex-wrap items-baseline justify-between gap-2">
                                    <span class="text-xs text-muted">
                                        @if ((float) $extra->price == 0)
                                            <span class="badge-green">{{ __('booking.free') }}</span>
                                        @else
                                            {{ money($extra->price) }} <span class="opacity-80">{{ $pricingLabels[$extra->pricing] ?? '' }}</span>
                                        @endif
                                    </span>
                                    @if ((float) $extra->price > 0)
                                        <span class="text-sm font-semibold {{ $on ? 'text-gold-700 dark:text-gold-300' : 'text-ink' }}">
                                            @if ($qty > 1)<span class="text-xs font-normal text-muted">{{ $qty }} × {{ money($extra->price) }} = </span>@endif{{ money($line) }}
                                        </span>
                                    @endif
                                </span>
                            </span>
                        </button>
                    @endforeach
                </div>

                {{-- Promo --}}
                <div class="card mt-8 p-5 sm:p-6">
                    <div class="flex items-center gap-3">
                        <span class="grid size-10 place-items-center rounded-full bg-elevated text-gold-600 dark:text-gold-300">@include('livewire.booking.partials.icon', ['name' => 'tag', 'class' => 'size-5'])</span>
                        <div>
                            <h3 class="font-sans text-base font-semibold">{{ __('booking.promo.title') }}</h3>
                            <p class="text-xs text-muted">{{ __('booking.promo.hint') }}</p>
                        </div>
                    </div>
                    @if ($promo)
                        <div class="mt-4 flex flex-wrap items-center justify-between gap-3 rounded-xl border border-emerald-300 bg-emerald-50 px-4 py-3 text-sm text-emerald-800 dark:border-emerald-800 dark:bg-emerald-950/40 dark:text-emerald-200">
                            <span class="flex items-center gap-2">
                                @include('livewire.booking.partials.icon', ['name' => 'check', 'class' => 'size-4', 'stroke' => 2.5])
                                <span><strong class="tracking-wider">{{ $promo }}</strong> — {{ __('booking.promo.saving', ['amount' => money($quote['discount'] ?? 0, true)]) }}</span>
                            </span>
                            <button type="button" wire:click="removePromo" class="text-xs font-semibold underline underline-offset-4">{{ __('booking.promo.remove') }}</button>
                        </div>
                    @else
                        <form wire:submit="applyPromo" class="mt-4 flex flex-col gap-2 sm:flex-row">
                            <label for="promo_input" class="sr-only">{{ __('booking.promo.title') }}</label>
                            <input id="promo_input" type="text" wire:model="promo_input" placeholder="{{ __('booking.promo.placeholder') }}" autocomplete="off"
                                   class="input uppercase tracking-wider placeholder:normal-case placeholder:tracking-normal @error('promo_input') border-rose-400 @enderror">
                            <button type="submit" class="btn-outline shrink-0" wire:loading.attr="disabled" wire:target="applyPromo">
                                <span wire:loading.remove wire:target="applyPromo">{{ __('booking.promo.apply') }}</span>
                                <span wire:loading wire:target="applyPromo">{{ __('booking.please_wait') }}</span>
                            </button>
                        </form>
                        @error('promo_input') <p class="input-error flex items-center gap-1">@include('livewire.booking.partials.icon', ['name' => 'warning', 'class' => 'size-3.5']) {{ $message }}</p> @enderror
                        <p class="mt-3 text-xs text-muted">{!! __('booking.promo.demo', ['a' => '<button type="button" wire:click="$set(\'promo_input\', \'WELCOME10\')" class="font-semibold text-gold-600 underline-offset-2 hover:underline dark:text-gold-300">WELCOME10</button>', 'b' => '<button type="button" wire:click="$set(\'promo_input\', \'AURORA20\')" class="font-semibold text-gold-600 underline-offset-2 hover:underline dark:text-gold-300">AURORA20</button>']) !!}</p>
                    @endif
                </div>

                <div class="mt-8 flex flex-col-reverse gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <button type="button" wire:click="goToStep(1)" class="btn-outline">@include('livewire.booking.partials.icon', ['name' => 'arrow-left', 'class' => 'size-4']) {{ __('booking.back') }}</button>
                    <button type="button" wire:click="continueToDetails" class="btn-gold">
                        {{ __('booking.continue_details') }} @include('livewire.booking.partials.icon', ['name' => 'arrow-right', 'class' => 'size-4'])
                    </button>
                </div>
            @endif

            {{-- ============ STEP 3: guest details ============ --}}
            @if ($step === 3 && $selected)
                <form wire:submit="continueToReview" novalidate>
                    <div class="flex flex-wrap items-end justify-between gap-3">
                        <div>
                            <h2 class="font-serif text-3xl">{{ __('booking.details_title') }}</h2>
                            <p class="mt-1 text-sm text-muted">{{ __('booking.details_text') }}</p>
                        </div>
                        @guest
                            <a href="{{ route('login') }}" x-data :href="@js(route('login')) + '?redirect=' + encodeURIComponent(location.pathname + location.search)" class="link text-sm font-semibold">{{ __('booking.have_account') }}</a>
                        @endguest
                    </div>

                    <div class="card mt-6 grid gap-5 p-5 sm:grid-cols-2 sm:p-6">
                        @foreach ([['first_name', 'given-name', 'text'], ['last_name', 'family-name', 'text'], ['email', 'email', 'email'], ['phone', 'tel', 'tel']] as [$f, $ac, $it])
                            <div>
                                <label for="{{ $f }}" class="label">{{ __('booking.fields.'.$f) }} <span class="text-gold-500">*</span></label>
                                <input id="{{ $f }}" type="{{ $it }}" wire:model.blur="{{ $f }}" autocomplete="{{ $ac }}" @if ($f === 'phone') placeholder="+1 555 000 0000" @endif
                                       class="input @error($f) border-rose-400 @enderror" @error($f) aria-invalid="true" aria-describedby="{{ $f }}-error" @enderror>
                                @error($f) <p id="{{ $f }}-error" class="input-error">{{ $message }}</p> @enderror
                            </div>
                        @endforeach
                        <div>
                            <label for="country" class="label">{{ __('booking.fields.country') }} <span class="text-gold-500">*</span></label>
                            <select id="country" wire:model.blur="country" autocomplete="country-name" class="input @error('country') border-rose-400 @enderror">
                                <option value="">{{ __('booking.fields.country_placeholder') }}</option>
                                @foreach ($countries as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('country') <p class="input-error">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="arrival_time" class="label">{{ __('booking.fields.arrival_time') }}</label>
                            <select id="arrival_time" wire:model="arrival_time" class="input">
                                <option value="">{{ __('booking.fields.arrival_unknown') }}</option>
                                @foreach ($arrivalTimes as $slot)
                                    <option value="{{ $slot }}">{{ $slot }}</option>
                                @endforeach
                            </select>
                            <p class="mt-1 text-xs text-muted">{{ __('booking.fields.arrival_hint', ['time' => setting('check_in_time')]) }}</p>
                        </div>
                        <div class="sm:col-span-2">
                            <label for="special_requests" class="label">{{ __('booking.fields.special_requests') }}</label>
                            <textarea id="special_requests" wire:model.blur="special_requests" rows="3" maxlength="1000" placeholder="{{ __('booking.fields.special_placeholder') }}" class="input resize-y"></textarea>
                            @error('special_requests') <p class="input-error">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    @guest
                        <div class="card mt-5 p-5 sm:p-6" x-data="{ on: $wire.entangle('create_account').live }">
                            <label class="flex cursor-pointer items-start gap-3">
                                <input type="checkbox" x-model="on" class="mt-0.5 size-5 rounded border-line text-gold-500 accent-gold-500 focus:ring-gold-500">
                                <span>
                                    <span class="block text-sm font-semibold">{{ __('booking.account.create') }}</span>
                                    <span class="block text-xs text-muted">{{ __('booking.account.create_text') }}</span>
                                </span>
                            </label>
                            <div x-show="on" x-collapse x-cloak>
                                <div class="mt-4 max-w-sm" x-data="{ show: false }">
                                    <label for="password" class="label">{{ __('booking.account.password') }}</label>
                                    <div class="relative">
                                        <input id="password" :type="show ? 'text' : 'password'" wire:model.blur="password" autocomplete="new-password" class="input pr-12 @error('password') border-rose-400 @enderror">
                                        <button type="button" @click="show = !show" class="absolute inset-y-0 right-0 grid w-11 place-items-center text-muted hover:text-ink" :aria-label="show ? @js(__('booking.account.hide_password')) : @js(__('booking.account.show_password'))">
                                            @include('livewire.booking.partials.icon', ['name' => 'eye', 'class' => 'size-5'])
                                        </button>
                                    </div>
                                    <p class="mt-1 text-xs text-muted">{{ __('booking.account.password_hint') }}</p>
                                    @error('password') <p class="input-error">{{ $message }}</p> @enderror
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="mt-5 flex items-center gap-3 rounded-2xl border border-line bg-elevated/60 px-5 py-4 text-sm">
                            @include('livewire.booking.partials.icon', ['name' => 'user', 'class' => 'size-5 text-gold-500'])
                            <span>{{ __('booking.account.signed_in', ['name' => auth()->user()->name]) }}</span>
                        </div>
                    @endguest

                    <div class="mt-5">
                        <label class="flex cursor-pointer items-start gap-3 text-sm">
                            <input type="checkbox" wire:model="terms" class="mt-0.5 size-5 rounded border-line accent-gold-500">
                            <span>{!! __('booking.terms_accept', ['terms' => '<a href="'.route('terms').'" target="_blank" class="link">'.e(__('booking.terms_inline')).'</a>', 'privacy' => '<a href="'.route('privacy').'" target="_blank" class="link">'.e(__('booking.privacy_inline')).'</a>']) !!}</span>
                        </label>
                        @error('terms') <p class="input-error ml-8">{{ $message }}</p> @enderror
                    </div>

                    <div class="mt-8 flex flex-col-reverse gap-3 sm:flex-row sm:items-center sm:justify-between">
                        <button type="button" wire:click="goToStep(2)" class="btn-outline">@include('livewire.booking.partials.icon', ['name' => 'arrow-left', 'class' => 'size-4']) {{ __('booking.back') }}</button>
                        <button type="submit" class="btn-gold" wire:loading.attr="disabled" wire:target="continueToReview">
                            {{ __('booking.continue_review') }} @include('livewire.booking.partials.icon', ['name' => 'arrow-right', 'class' => 'size-4'])
                        </button>
                    </div>
                </form>
            @endif

            {{-- ============ STEP 4: review & payment ============ --}}
            @if ($step === 4 && $selected)
                <div>
                    <h2 class="font-serif text-3xl">{{ __('booking.review_title') }}</h2>
                    <p class="mt-1 text-sm text-muted">{{ __('booking.review_text') }}</p>
                </div>

                <div class="card mt-6 divide-y divide-line">
                    <div class="flex flex-col gap-4 p-5 sm:flex-row sm:p-6">
                        <img src="{{ $type->cover }}" alt="{{ $type->name }}" class="aspect-[4/3] w-full rounded-xl object-cover sm:w-40">
                        <div class="min-w-0 flex-1">
                            <div class="flex items-start justify-between gap-3">
                                <h3 class="font-serif text-2xl">{{ $type->name }}</h3>
                                <button type="button" wire:click="goToStep(1)" class="link shrink-0 text-xs font-semibold">{{ __('booking.change') }}</button>
                            </div>
                            <dl class="mt-3 grid grid-cols-2 gap-3 text-sm sm:grid-cols-3">
                                <div><dt class="text-xs text-muted">{{ __('booking.check_in') }}</dt><dd class="font-medium">{{ $fmt($check_in) }}</dd><dd class="text-xs text-muted">{{ __('booking.from_time', ['time' => setting('check_in_time')]) }}</dd></div>
                                <div><dt class="text-xs text-muted">{{ __('booking.check_out') }}</dt><dd class="font-medium">{{ $fmt($check_out) }}</dd><dd class="text-xs text-muted">{{ __('booking.until_time', ['time' => setting('check_out_time')]) }}</dd></div>
                                <div><dt class="text-xs text-muted">{{ __('booking.guests') }}</dt><dd class="font-medium">{{ $guestsLabel }}</dd><dd class="text-xs text-muted">{{ trans_choice('booking.nights_count', $this->nights) }}</dd></div>
                            </dl>
                        </div>
                    </div>
                    <div class="grid gap-4 p-5 text-sm sm:grid-cols-2 sm:p-6">
                        <div>
                            <div class="flex items-center justify-between"><p class="label mb-0">{{ __('booking.guest') }}</p><button type="button" wire:click="goToStep(3)" class="link text-xs font-semibold">{{ __('booking.edit') }}</button></div>
                            <p class="mt-1 font-medium">{{ $first_name }} {{ $last_name }}</p>
                            <p class="text-muted">{{ $email }}</p>
                            <p class="text-muted">{{ $phone }} · {{ $countries[$country] ?? $country }}</p>
                        </div>
                        <div>
                            <p class="label mb-0">{{ __('booking.fields.arrival_time') }}</p>
                            <p class="mt-1">{{ $arrival_time ?: __('booking.fields.arrival_unknown') }}</p>
                            @if ($special_requests)
                                <p class="label mt-3 mb-0">{{ __('booking.fields.special_requests') }}</p>
                                <p class="mt-1 text-muted">{{ \Illuminate\Support\Str::limit($special_requests, 160) }}</p>
                            @endif
                        </div>
                    </div>
                </div>

                <h3 class="mt-8 font-serif text-2xl">{{ __('booking.payment_title') }}</h3>
                <div class="mt-4 grid gap-4 md:grid-cols-3" role="radiogroup" aria-label="{{ __('booking.payment_title') }}">
                    @foreach (['card' => ['credit-card', __('booking.pay_card'), __('booking.pay_card_text')], 'idram' => ['wallet', __('booking.pay_idram'), __('booking.pay_idram_text')], 'on_arrival' => ['building', __('booking.pay_hotel'), __('booking.pay_hotel_text')]] as $value => [$icon, $label, $text])
                        <label wire:key="pm-{{ $value }}" class="card relative flex cursor-pointer gap-4 p-5 transition hover:shadow-md {{ $payment_method === $value ? 'border-gold-400 ring-2 ring-gold-400/70' : '' }}">
                            <input type="radio" wire:model.live="payment_method" value="{{ $value }}" class="sr-only">
                            <span class="grid size-11 shrink-0 place-items-center rounded-xl {{ $payment_method === $value ? 'bg-gold-400 text-midnight-900' : 'bg-elevated text-gold-600 dark:text-gold-300' }}">
                                @include('livewire.booking.partials.icon', ['name' => $icon, 'class' => 'size-6'])
                            </span>
                            <span class="min-w-0 pr-6">
                                <span class="flex flex-wrap items-center gap-x-2 gap-y-1 font-semibold">{{ $label }} @if ($value !== 'on_arrival')<span class="badge-gold">{{ __('booking.demo') }}</span>@endif</span>
                                <span class="mt-1 block text-xs text-muted">{{ $text }}</span>
                            </span>
                            <span class="absolute top-4 right-4 grid size-5 place-items-center rounded-full border-2 {{ $payment_method === $value ? 'border-gold-500' : 'border-line' }}">
                                @if ($payment_method === $value)<span class="size-2.5 rounded-full bg-gold-500"></span>@endif
                            </span>
                        </label>
                    @endforeach
                </div>

                <div class="mt-5 flex items-start gap-3 rounded-2xl bg-elevated/70 p-4 text-xs text-muted">
                    @include('livewire.booking.partials.icon', ['name' => 'shield', 'class' => 'size-5 shrink-0 text-gold-500'])
                    <p>{{ __('booking.cancellation_policy', ['hours' => setting('free_cancellation_hours')]) }}</p>
                </div>

                <div class="mt-8 flex flex-col-reverse gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <button type="button" wire:click="goToStep(3)" class="btn-outline">@include('livewire.booking.partials.icon', ['name' => 'arrow-left', 'class' => 'size-4']) {{ __('booking.back') }}</button>
                    <button type="button" wire:click="book" wire:loading.attr="disabled" wire:target="book" class="btn-gold px-8 py-4 text-base">
                        <svg wire:loading wire:target="book" class="size-5 animate-spin" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="9" stroke="currentColor" stroke-opacity=".3" stroke-width="3"/><path d="M21 12a9 9 0 0 0-9-9" stroke="currentColor" stroke-width="3" stroke-linecap="round"/></svg>
                        <span wire:loading.remove wire:target="book">
                            {{ $payment_method === 'card' ? __('booking.submit_card', ['amount' => money($quote['total'] ?? 0, true)]) : ($payment_method === 'idram' ? __('booking.submit_idram', ['amount' => money($quote['total'] ?? 0, true)]) : __('booking.submit_hotel')) }}
                        </span>
                        <span wire:loading wire:target="book">{{ __('booking.creating') }}</span>
                    </button>
                </div>
            @endif
        </div>

        {{-- Desktop summary --}}
        <aside class="hidden lg:col-span-4 lg:block">
            <div class="sticky top-24">
                @include('livewire.booking.partials.summary', ['mobile' => false])
            </div>
        </aside>
    </div>
</div>
