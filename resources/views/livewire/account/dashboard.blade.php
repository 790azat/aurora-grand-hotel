@php
    $groups = $this->groups;
    $stats = $this->stats;
    $firstName = explode(' ', trim(auth()->user()->name))[0];
@endphp
<div>
    @include('livewire.account.partials.header', ['heading' => __('account.dashboard.hello', ['name' => $firstName]), 'sub' => auth()->user()->email])

    <div class="container-x py-10 sm:py-12">
        <div class="grid gap-6 lg:grid-cols-3">
            {{-- Upcoming stay --}}
            <div class="lg:col-span-2">
                @if ($next)
                    @php
                        $arrival = $next->check_in->copy()->setTimeFromTimeString(setting('check_in_time'));
                    @endphp
                    <article class="card group relative overflow-hidden">
                        <div class="grid md:grid-cols-5">
                            <div class="relative aspect-[16/10] md:col-span-2 md:aspect-auto md:min-h-64">
                                <img src="{{ $next->roomType->cover }}" alt="{{ $next->roomType->name }}" class="absolute inset-0 size-full object-cover">
                                <div class="absolute inset-0 bg-gradient-to-t from-midnight-950/70 to-transparent md:bg-gradient-to-r"></div>
                                <span class="badge-gold absolute top-3 left-3">{{ $next->status === 'checked_in' ? __('account.dashboard.in_house') : __('account.dashboard.next_stay') }}</span>
                            </div>
                            <div class="flex flex-col p-5 sm:p-6 md:col-span-3">
                                <div class="flex flex-wrap items-start justify-between gap-2">
                                    <div>
                                        <h2 class="font-serif text-3xl">{{ $next->roomType->name }}</h2>
                                        <p class="mt-1 text-sm text-muted">{{ $next->check_in->translatedFormat('D, j M') }} — {{ $next->check_out->translatedFormat('D, j M Y') }} · {{ trans_choice('booking.nights_count', $next->nights) }}</p>
                                    </div>
                                    <span class="flex flex-wrap gap-1.5">@include('livewire.booking.partials.status-badge', ['booking' => $next])</span>
                                </div>

                                @if ($next->status !== 'checked_in' && $arrival->isFuture())
                                    <div class="mt-5 grid grid-cols-4 gap-2 text-center" x-data="{
                                            target: {{ $arrival->getTimestamp() * 1000 }}, d: 0, h: 0, m: 0, s: 0,
                                            tick() { let t = Math.max(0, this.target - Date.now()) / 1000; this.d = Math.floor(t / 86400); this.h = Math.floor(t % 86400 / 3600); this.m = Math.floor(t % 3600 / 60); this.s = Math.floor(t % 60); }
                                         }" x-init="tick(); setInterval(() => tick(), 1000)">
                                        @foreach (['d' => __('account.dashboard.days'), 'h' => __('account.dashboard.hours'), 'm' => __('account.dashboard.minutes'), 's' => __('account.dashboard.seconds')] as $k => $label)
                                            <div class="rounded-xl bg-elevated px-2 py-3">
                                                <span class="block font-serif text-3xl font-semibold tabular-nums text-ink" x-text="String({{ $k }}).padStart(2, '0')">--</span>
                                                <span class="block text-[10px] font-semibold tracking-wider text-muted uppercase">{{ $label }}</span>
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <p class="mt-5 rounded-xl bg-emerald-50 p-4 text-sm text-emerald-800 dark:bg-emerald-950/40 dark:text-emerald-200">{{ __('account.dashboard.enjoy') }}</p>
                                @endif

                                <div class="mt-auto flex flex-wrap gap-2 pt-5">
                                    <a href="{{ route('account.bookings.show', $next) }}" wire:navigate class="btn-dark btn-sm">{{ __('account.dashboard.manage') }}</a>
                                    @if ($next->payment_method === 'card' && $next->balance > 0 && in_array($next->status, ['pending', 'confirmed'], true))
                                        <a href="{{ route('booking.pay', $next) }}" class="btn-gold btn-sm">{{ __('booking.pay_now_amount', ['amount' => money($next->balance, true)]) }}</a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </article>
                @else
                    <div class="card flex h-full flex-col items-center justify-center px-6 py-12 text-center">
                        <span class="grid size-14 place-items-center rounded-full bg-elevated text-gold-500">@include('livewire.booking.partials.icon', ['name' => 'calendar', 'class' => 'size-7'])</span>
                        <h2 class="mt-4 font-serif text-3xl">{{ __('account.dashboard.no_upcoming') }}</h2>
                        <p class="mt-2 max-w-sm text-sm text-muted">{{ __('account.dashboard.no_upcoming_text') }}</p>
                        <a href="{{ route('booking') }}" class="btn-gold mt-6">{{ __('account.nav.book') }}</a>
                    </div>
                @endif
            </div>

            {{-- Stats --}}
            <div class="grid grid-cols-3 gap-3 lg:grid-cols-1">
                @foreach ([['moon', __('account.dashboard.stat_stays'), $stats['stays']], ['bed', __('account.dashboard.stat_nights'), $stats['nights']], ['credit-card', __('account.dashboard.stat_spent'), money($stats['spent'])]] as [$icon, $label, $value])
                    <div class="card flex flex-col gap-3 p-4 sm:p-5 lg:flex-row lg:items-center">
                        <span class="grid size-10 shrink-0 place-items-center rounded-full bg-gold-100 text-gold-700 dark:bg-gold-900/50 dark:text-gold-200 sm:size-12">@include('livewire.booking.partials.icon', ['name' => $icon, 'class' => 'size-5'])</span>
                        <span>
                            <span class="block font-serif text-2xl font-semibold sm:text-3xl">{{ $value }}</span>
                            <span class="block text-xs text-muted">{{ $label }}</span>
                        </span>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Bookings --}}
        <div class="mt-12 flex flex-wrap items-end justify-between gap-4">
            <h2 class="font-serif text-3xl">{{ __('account.dashboard.my_bookings') }}</h2>
            <div class="flex rounded-full border border-line bg-surface p-1 text-sm" role="tablist">
                @foreach (\App\Livewire\Account\Dashboard::TABS as $t)
                    <button type="button" role="tab" aria-selected="{{ $tab === $t ? 'true' : 'false' }}" wire:click="setTab('{{ $t }}')"
                            class="rounded-full px-4 py-1.5 font-semibold transition {{ $tab === $t ? 'bg-midnight-900 text-white dark:bg-gold-400 dark:text-midnight-950' : 'text-muted hover:text-ink' }}">
                        {{ __('account.dashboard.tabs.'.$t) }} <span class="ml-0.5 text-xs opacity-70">{{ $groups[$t]->count() }}</span>
                    </button>
                @endforeach
            </div>
        </div>

        <div class="mt-6 space-y-3" wire:loading.class="opacity-60" wire:target="setTab">
            @forelse ($groups[$tab] as $b)
                <a href="{{ route('account.bookings.show', $b) }}" wire:navigate wire:key="bk-{{ $b->id }}" class="card group flex flex-col gap-4 p-4 transition hover:-translate-y-0.5 hover:shadow-lg sm:flex-row sm:items-center sm:p-5">
                    <img src="{{ $b->roomType->cover }}" alt="" class="aspect-[16/9] w-full rounded-xl object-cover sm:aspect-square sm:size-20">
                    <div class="min-w-0 flex-1">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="font-mono text-xs font-semibold tracking-wider text-gold-600 dark:text-gold-300">{{ $b->reference }}</span>
                            @include('livewire.booking.partials.status-badge', ['booking' => $b])
                        </div>
                        <p class="mt-1 font-serif text-xl">{{ $b->roomType->name }}</p>
                        <p class="text-sm text-muted">{{ $b->check_in->translatedFormat('j M') }} — {{ $b->check_out->translatedFormat('j M Y') }} · {{ trans_choice('booking.nights_count', $b->nights) }} · {{ trans_choice('booking.adults_count', $b->adults) }}@if ($b->children), {{ trans_choice('booking.children_count', $b->children) }}@endif</p>
                    </div>
                    <div class="flex items-center justify-between gap-4 sm:flex-col sm:items-end">
                        <span class="font-serif text-2xl font-semibold">{{ money($b->total) }}</span>
                        <span class="flex items-center gap-1 text-xs font-semibold text-gold-600 group-hover:gap-2 dark:text-gold-300">{{ __('account.dashboard.details') }} @include('livewire.booking.partials.icon', ['name' => 'arrow-right', 'class' => 'size-3.5 transition-all'])</span>
                    </div>
                </a>
            @empty
                <div class="rounded-2xl border border-dashed border-line px-6 py-12 text-center">
                    <p class="font-serif text-2xl">{{ __('account.dashboard.empty.'.$tab) }}</p>
                    <p class="mt-1 text-sm text-muted">{{ __('account.dashboard.empty_text') }}</p>
                    @if ($tab === 'upcoming')
                        <a href="{{ route('booking') }}" class="btn-gold btn-sm mt-5">{{ __('account.nav.book') }}</a>
                    @endif
                </div>
            @endforelse
        </div>
    </div>
</div>
