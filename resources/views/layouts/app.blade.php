@php
    $hotel = setting('hotel_name');
    $pageTitle = isset($title) && $title ? $title.' · '.$hotel : $hotel.' — '.\App\Models\Setting::localized('hotel_tagline');
    $pageDescription = $description ?? __('site.meta_description');
    $nav = [
        ['route' => 'rooms.index', 'label' => __('site.nav.rooms')],
        ['route' => 'facilities', 'label' => __('site.nav.facilities')],
        ['route' => 'offers', 'label' => __('site.nav.offers')],
        ['route' => 'gallery', 'label' => __('site.nav.gallery')],
        ['route' => 'about', 'label' => __('site.nav.about')],
        ['route' => 'blog.index', 'label' => __('site.nav.blog')],
        ['route' => 'contact', 'label' => __('site.nav.contact')],
    ];
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $pageTitle }}</title>
    <meta name="description" content="{{ $pageDescription }}">
    <meta property="og:title" content="{{ $pageTitle }}">
    <meta property="og:description" content="{{ $pageDescription }}">
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:image" content="{{ $ogImage ?? \Database\Seeders\DatabaseSeeder::img('1566073771259-6a8506099945', 1200) }}">
    <link rel="canonical" href="{{ url()->current() }}">
    @foreach (\App\Http\Middleware\SetLocale::LOCALES as $code => $name)
        <link rel="alternate" hreflang="{{ $code }}" href="{{ route('locale.switch', $code) }}">
    @endforeach
    <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 64 64'><rect width='64' height='64' rx='14' fill='%230b1118'/><text x='32' y='44' font-family='Georgia,serif' font-size='34' fill='%23cba65a' text-anchor='middle'>A</text></svg>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@400;500;600;700&family=Manrope:wght@400;500;600;700&family=Noto+Sans+Armenian:wght@400;500;600;700&family=Noto+Serif+Armenian:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script>
        try {
            const t = localStorage.getItem('theme');
            if (t === 'dark' || (!t && window.matchMedia('(prefers-color-scheme: dark)').matches)) document.documentElement.classList.add('dark');
        } catch (e) {}
        // Early image fallback (images can fail before the deferred app bundle runs; app.js handles later ones).
        document.addEventListener('error', function (e) {
            var img = e.target;
            if (img.tagName !== 'IMG' || img.src.indexOf('data:') === 0 || img.hasAttribute('data-nofallback')) return;
            img.dataset.fallback = '1';
            img.removeAttribute('srcset');
            img.src = 'data:image/svg+xml;charset=utf-8,' + encodeURIComponent('<svg xmlns="http://www.w3.org/2000/svg" width="800" height="600"><defs><linearGradient id="g" x1="0" y1="0" x2="1" y2="1"><stop offset="0" stop-color="#1b2633"/><stop offset=".6" stop-color="#3d2f19"/><stop offset="1" stop-color="#b8914a"/></linearGradient></defs><rect width="800" height="600" fill="url(#g)"/><text x="400" y="310" font-family="Georgia,serif" font-size="34" fill="#e7d3a6" text-anchor="middle" letter-spacing="6">AURORA GRAND</text></svg>');
        }, true);
    </script>
    @isset($schema)
        <script type="application/ld+json">{!! $schema !!}</script>
    @endisset
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    @stack('head')
</head>
<body class="min-h-screen flex flex-col">
    <a href="#main" class="sr-only focus:not-sr-only focus:fixed focus:top-2 focus:left-2 focus:z-[100] btn-gold">{{ __('site.skip_to_content') }}</a>

    {{-- Header --}}
    <header x-data="{ open: false, scrolled: false }" x-init="scrolled = window.scrollY > 20" @scroll.window="scrolled = window.scrollY > 20"
            :class="scrolled || open || !{{ request()->routeIs('home') ? 'true' : 'false' }} ? 'bg-page/90 backdrop-blur-xl border-line shadow-sm text-ink' : 'bg-transparent border-transparent text-white'"
            class="fixed inset-x-0 top-0 z-50 border-b transition-all duration-300">
        <div class="container-x flex h-18 items-center justify-between gap-4 py-3">
            <a href="{{ route('home') }}" wire:navigate class="flex flex-col leading-none">
                <span class="font-serif text-2xl font-semibold tracking-[0.18em]">AURORA</span>
                <span class="text-[10px] font-semibold tracking-[0.42em] text-gold-400">GRAND · ★★★★★</span>
            </a>

            {{-- Russian and Armenian labels are longer: switch to the burger menu below xl. --}}
            @php($longNav = app()->getLocale() !== 'en')
            <nav class="hidden items-center gap-5 text-sm font-medium whitespace-nowrap 2xl:gap-6 {{ $longNav ? 'xl:flex' : 'lg:flex' }}" aria-label="Main">
                @foreach ($nav as $item)
                    <a href="{{ route($item['route']) }}" wire:navigate
                       class="transition hover:text-gold-400 {{ request()->routeIs($item['route'].'*') ? 'text-gold-400' : '' }}">{{ $item['label'] }}</a>
                @endforeach
            </nav>

            <div class="flex items-center gap-1 sm:gap-2">
                {{-- Language --}}
                <div x-data="{ o: false }" class="relative">
                    <button @click="o = !o" @click.outside="o = false" class="flex items-center gap-1 rounded-full px-2.5 py-2 text-xs font-bold uppercase tracking-wider hover:text-gold-400" aria-label="{{ __('site.language') }}">
                        <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 21a9 9 0 1 0 0-18 9 9 0 0 0 0 18Zm0 0c2.5-2.5 3.75-5.5 3.75-9S14.5 5.5 12 3m0 18c-2.5-2.5-3.75-5.5-3.75-9S9.5 5.5 12 3M3.5 9h17M3.5 15h17"/></svg>
                        {{ app()->getLocale() }}
                    </button>
                    <div x-show="o" x-cloak x-transition class="card absolute right-0 mt-2 w-36 overflow-hidden py-1 text-sm text-ink">
                        @foreach (\App\Http\Middleware\SetLocale::LOCALES as $code => $name)
                            <a href="{{ route('locale.switch', $code) }}" class="block px-4 py-2 hover:bg-elevated {{ app()->getLocale() === $code ? 'font-semibold text-gold-600 dark:text-gold-300' : '' }}">{{ $name }}</a>
                        @endforeach
                    </div>
                </div>

                {{-- Theme --}}
                <button onclick="toggleTheme()" class="rounded-full p-2 hover:text-gold-400" aria-label="{{ __('site.toggle_theme') }}">
                    <svg class="size-5 dark:hidden" fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21.75 15.002A9.72 9.72 0 0 1 18 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 0 0 3 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 0 0 9-5.998Z"/></svg>
                    <svg class="hidden size-5 dark:block" fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3v2.25m6.364.386-1.591 1.591M21 12h-2.25m-.386 6.364-1.591-1.591M12 18.75V21m-4.773-4.227-1.591 1.591M5.25 12H3m4.227-4.773L5.636 5.636M15.75 12a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0Z"/></svg>
                </button>

                {{-- Account --}}
                <a href="{{ auth()->check() ? (auth()->user()->isStaff() ? url('/admin') : route('account.dashboard')) : route('login') }}" class="rounded-full p-2 hover:text-gold-400" aria-label="{{ __('site.nav.account') }}">
                    <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z"/></svg>
                </a>

                <a href="{{ route('booking') }}" wire:navigate class="btn-gold btn-sm hidden sm:inline-flex">{{ __('site.book_now') }}</a>

                <button @click="open = !open" class="rounded-full p-2 {{ $longNav ? 'xl:hidden' : 'lg:hidden' }}" :aria-expanded="open" aria-label="Menu">
                    <svg x-show="!open" class="size-6" fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor"><path stroke-linecap="round" d="M3.75 7h16.5M3.75 12h16.5M3.75 17h16.5"/></svg>
                    <svg x-show="open" x-cloak class="size-6" fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor"><path stroke-linecap="round" d="M6 18 18 6M6 6l12 12"/></svg>
                </button>
            </div>
        </div>

        {{-- Mobile menu --}}
        <div x-show="open" x-cloak x-transition.opacity class="border-t border-line bg-page {{ $longNav ? 'xl:hidden' : 'lg:hidden' }}">
            <nav class="container-x flex flex-col py-4 text-ink">
                @foreach ($nav as $item)
                    <a href="{{ route($item['route']) }}" wire:navigate class="border-b border-line/60 py-3 font-serif text-xl">{{ $item['label'] }}</a>
                @endforeach
                <a href="{{ route('booking') }}" wire:navigate class="btn-gold mt-4">{{ __('site.book_now') }}</a>
            </nav>
        </div>
    </header>

    <main id="main" class="flex-1 {{ request()->routeIs('home') ? '' : 'pt-18' }}">
        {{ $slot }}
    </main>

    {{-- Footer --}}
    <footer class="mt-auto bg-midnight-950 text-gray-300">
        <div class="container-x grid gap-12 py-16 md:grid-cols-2 lg:grid-cols-4">
            <div>
                <div class="font-serif text-3xl tracking-[0.18em] text-white">AURORA</div>
                <div class="text-[10px] font-semibold tracking-[0.42em] text-gold-400">GRAND HOTEL & SPA</div>
                <p class="mt-5 text-sm leading-relaxed text-gray-400">{{ \App\Models\Setting::localized('hotel_tagline') }}</p>
                <div class="mt-6 flex gap-3">
                    @foreach (['instagram' => 'M7.75 2h8.5A5.75 5.75 0 0 1 22 7.75v8.5A5.75 5.75 0 0 1 16.25 22h-8.5A5.75 5.75 0 0 1 2 16.25v-8.5A5.75 5.75 0 0 1 7.75 2Zm4.25 5a5 5 0 1 0 0 10 5 5 0 0 0 0-10Zm5.5-.75a1 1 0 1 0 0 2 1 1 0 0 0 0-2Z', 'facebook' => 'M14 8h3V4h-3a4 4 0 0 0-4 4v2H8v4h2v8h4v-8h3l1-4h-4V8Z', 'telegram' => 'M21.5 3.5 2.8 10.7c-1 .4-1 1.8.1 2.1l4.6 1.4 1.8 5.6c.3.9 1.4 1.1 2 .4l2.6-2.7 4.6 3.4c.8.6 1.9.1 2.1-.8L23 4.9c.2-1-.7-1.8-1.5-1.4ZM9.6 14.6l8.2-7.2-6.6 8.6-.4 3-1.2-4.4Z'] as $net => $path)
                        <a href="{{ setting($net) }}" target="_blank" rel="noopener" class="grid size-10 place-items-center rounded-full border border-white/15 transition hover:border-gold-400 hover:text-gold-400" aria-label="{{ ucfirst($net) }}">
                            <svg class="size-4" fill="currentColor" viewBox="0 0 24 24"><path d="{{ $path }}"/></svg>
                        </a>
                    @endforeach
                </div>
            </div>
            <div>
                <h3 class="font-sans text-xs font-semibold uppercase tracking-[0.25em] text-gold-400">{{ __('site.footer.explore') }}</h3>
                <ul class="mt-5 space-y-3 text-sm">
                    @foreach (array_merge($nav, [['route' => 'faq', 'label' => __('site.nav.faq')], ['route' => 'reviews', 'label' => __('site.nav.reviews')], ['route' => 'booking.lookup', 'label' => __('site.nav.manage_booking')]]) as $item)
                        <li><a href="{{ route($item['route']) }}" wire:navigate class="hover:text-gold-400">{{ $item['label'] }}</a></li>
                    @endforeach
                </ul>
            </div>
            <div>
                <h3 class="font-sans text-xs font-semibold uppercase tracking-[0.25em] text-gold-400">{{ __('site.footer.contact') }}</h3>
                <ul class="mt-5 space-y-3 text-sm text-gray-400">
                    <li>{{ \App\Models\Setting::localized('hotel_address') }}</li>
                    <li><a href="tel:{{ preg_replace('/[^\d+]/', '', setting('hotel_phone')) }}" class="hover:text-gold-400">{{ setting('hotel_phone') }}</a></li>
                    <li><a href="mailto:{{ setting('hotel_email') }}" class="hover:text-gold-400">{{ setting('hotel_email') }}</a></li>
                    <li>{{ __('site.footer.check_in_out', ['in' => setting('check_in_time'), 'out' => setting('check_out_time')]) }}</li>
                </ul>
            </div>
            <div>
                <h3 class="font-sans text-xs font-semibold uppercase tracking-[0.25em] text-gold-400">{{ __('site.footer.newsletter') }}</h3>
                <p class="mt-5 text-sm text-gray-400">{{ __('site.footer.newsletter_text') }}</p>
                <div class="mt-4"><livewire:newsletter-form /></div>
            </div>
        </div>
        <div class="border-t border-white/10">
            <div class="container-x flex flex-col items-center justify-between gap-3 py-6 text-xs text-gray-500 sm:flex-row">
                <p>© {{ date('Y') }} {{ setting('hotel_name') }}. {{ __('site.footer.rights') }}</p>
                <div class="flex gap-5">
                    <a href="{{ route('privacy') }}" class="hover:text-gold-400">{{ __('site.footer.privacy') }}</a>
                    <a href="{{ route('terms') }}" class="hover:text-gold-400">{{ __('site.footer.terms') }}</a>
                </div>
            </div>
        </div>
    </footer>

    {{-- Live chat (persists across wire:navigate page changes) --}}
    @persist('chat')
        <livewire:chat-widget />
    @endpersist

    {{-- Toasts: dispatch('notify', message: '...', type: 'success'|'error') --}}
    <div x-data="{ toasts: [] }"
         @notify.window="const t = { id: Date.now(), ...$event.detail }; toasts.push(t); setTimeout(() => toasts = toasts.filter(x => x.id !== t.id), 4500)"
         class="pointer-events-none fixed inset-x-0 top-20 z-[60] flex flex-col items-center gap-2 px-4">
        <template x-for="t in toasts" :key="t.id">
            <div x-transition class="pointer-events-auto card flex items-center gap-3 px-5 py-3 text-sm shadow-xl"
                 :class="t.type === 'error' ? 'border-rose-400' : 'border-gold-400'">
                <span x-text="t.type === 'error' ? '⚠️' : '✓'" class="font-bold text-gold-500"></span>
                <span x-text="t.message"></span>
            </div>
        </template>
    </div>
    @if (session('status'))
        <div x-data x-init="$nextTick(() => $dispatch('notify', { message: @js(session('status')) }))"></div>
    @endif

    @livewireScripts
    @stack('scripts')
</body>
</html>
