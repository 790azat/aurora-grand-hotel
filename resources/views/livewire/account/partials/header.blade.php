{{-- Account header + navigation. Expects $heading, optional $sub. --}}
@php
    $links = [
        ['route' => 'account.dashboard', 'label' => __('account.nav.overview'), 'icon' => 'home', 'active' => request()->routeIs('account.dashboard', 'account.bookings.*')],
        ['route' => 'account.profile', 'label' => __('account.nav.profile'), 'icon' => 'cog', 'active' => request()->routeIs('account.profile')],
    ];
    $initials = collect(explode(' ', (string) auth()->user()->name))->filter()->take(2)->map(fn ($p) => mb_strtoupper(mb_substr($p, 0, 1)))->implode('');
@endphp
<section class="relative overflow-hidden bg-midnight-900 text-white">
    <div class="pointer-events-none absolute inset-0 opacity-40" style="background: radial-gradient(60% 120% at 90% 0%, rgba(203,166,90,.35), transparent 60%);"></div>
    <div class="container-x relative pt-10 sm:pt-14">
        <div class="flex flex-wrap items-center justify-between gap-6">
            <div class="flex items-center gap-4">
                <span class="grid size-14 place-items-center rounded-full bg-gold-400 font-serif text-2xl font-semibold text-midnight-900 sm:size-16">{{ $initials ?: 'A' }}</span>
                <div>
                    <p class="eyebrow text-gold-300">{{ __('account.eyebrow') }}</p>
                    <h1 class="mt-1 font-serif text-3xl sm:text-4xl">{{ $heading }}</h1>
                    @isset($sub)<p class="mt-1 text-sm text-gray-400">{{ $sub }}</p>@endisset
                </div>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('booking') }}" class="btn-gold btn-sm">@include('livewire.booking.partials.icon', ['name' => 'calendar', 'class' => 'size-4']) {{ __('account.nav.book') }}</a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="btn-ghost-light btn-sm">@include('livewire.booking.partials.icon', ['name' => 'logout', 'class' => 'size-4']) {{ __('account.nav.logout') }}</button>
                </form>
            </div>
        </div>
        <nav class="mt-8 flex gap-6 text-sm font-semibold" aria-label="{{ __('account.eyebrow') }}">
            @foreach ($links as $l)
                <a href="{{ route($l['route']) }}" wire:navigate class="flex items-center gap-2 border-b-2 pb-3 transition {{ $l['active'] ? 'border-gold-400 text-white' : 'border-transparent text-gray-400 hover:text-white' }}">
                    @include('livewire.booking.partials.icon', ['name' => $l['icon'], 'class' => 'size-4']) {{ $l['label'] }}
                </a>
            @endforeach
        </nav>
    </div>
</section>
