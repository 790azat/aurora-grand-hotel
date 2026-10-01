<div>
    <x-page-hero :eyebrow="__('site.about.eyebrow')" :title="__('site.about.title')" :subtitle="__('site.about.subtitle')"
                 image="https://images.unsplash.com/photo-1542314831-068cd1dbfeeb?auto=format&fit=crop&w=2000&q=80" />

    {{-- Story --}}
    <section class="section">
        <div class="container-x grid items-center gap-12 lg:grid-cols-2 lg:gap-20">
            <div class="relative">
                <img src="https://images.unsplash.com/photo-1566073771259-6a8506099945?auto=format&fit=crop&w=1000&q=80" alt="{{ __('site.about.story_img') }}" loading="lazy" class="aspect-[4/5] w-full rounded-3xl object-cover shadow-xl">
                <div class="absolute -right-4 -bottom-6 max-w-[16rem] rounded-3xl bg-midnight-900 p-6 text-white shadow-2xl sm:-right-8">
                    <p class="font-serif text-5xl text-gold-300">1998</p>
                    <p class="mt-2 text-sm text-white/75">{{ __('site.about.founded') }}</p>
                </div>
            </div>
            <div>
                <x-section-heading align="left" :eyebrow="__('site.about.story_eyebrow')" :title="__('site.about.story_title')" />
                @foreach (__('site.about.story') as $paragraph)
                    <p class="mt-5 leading-relaxed text-muted {{ $loop->first ? 'lead text-ink' : '' }}">{{ $paragraph }}</p>
                @endforeach
                <p class="mt-8 font-serif text-2xl italic text-gold-600 dark:text-gold-300">{{ __('site.about.team.0.name') }}</p>
                <p class="text-xs uppercase tracking-wider text-muted">{{ __('site.about.gm') }}</p>
            </div>
        </div>
    </section>

    {{-- Stats --}}
    <section class="bg-midnight-950 py-16 text-white">
        <dl class="container-x grid grid-cols-2 gap-8 text-center lg:grid-cols-4">
            @foreach ($stats as $stat)
                <div>
                    <dd class="font-serif text-5xl text-gold-300 sm:text-6xl">{{ $stat['value'] }}</dd>
                    <dt class="mt-2 text-xs font-semibold uppercase tracking-[0.2em] text-white/60">{{ $stat['label'] }}</dt>
                </div>
            @endforeach
        </dl>
    </section>

    {{-- Values --}}
    <section class="section">
        <div class="container-x">
            <x-section-heading :eyebrow="__('site.about.values_eyebrow')" :title="__('site.about.values_title')" />
            <div class="mt-12 grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                @foreach (__('site.about.values') as $value)
                    <div class="card p-7 transition hover:-translate-y-1 hover:shadow-lg">
                        <span class="grid size-12 place-items-center rounded-full bg-gold-100 text-gold-700 dark:bg-gold-900/50 dark:text-gold-200"><x-hotel-icon :name="$value['icon']" class="size-6" /></span>
                        <h3 class="mt-5 font-serif text-2xl">{{ $value['title'] }}</h3>
                        <p class="mt-2 text-sm leading-relaxed text-muted">{{ $value['text'] }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- Team --}}
    <section class="section bg-elevated/60">
        <div class="container-x">
            <x-section-heading :eyebrow="__('site.about.team_eyebrow')" :title="__('site.about.team_title')" :subtitle="__('site.about.team_text')" />
            @php
                $photos = ['1580489944761-15a19d654956', '1507003211169-0a1dd7228f2d', '1438761681033-6461ffad8d80', '1472099645785-5658abf4ff4e'];
            @endphp
            <div class="mt-12 grid gap-8 sm:grid-cols-2 lg:grid-cols-4">
                @foreach (__('site.about.team') as $i => $member)
                    <figure class="group text-center">
                        <div class="overflow-hidden rounded-3xl bg-elevated">
                            <img src="https://images.unsplash.com/photo-{{ $photos[$i % 4] }}?auto=format&fit=crop&w=600&h=750&q=80" alt="{{ $member['name'] }}" loading="lazy" class="aspect-[4/5] w-full object-cover grayscale transition duration-700 group-hover:scale-105 group-hover:grayscale-0">
                        </div>
                        <figcaption class="mt-4">
                            <p class="font-serif text-2xl">{{ $member['name'] }}</p>
                            <p class="text-xs font-semibold uppercase tracking-wider text-gold-600 dark:text-gold-300">{{ $member['role'] }}</p>
                            <p class="mt-2 text-sm text-muted">{{ $member['bio'] }}</p>
                        </figcaption>
                    </figure>
                @endforeach
            </div>
        </div>
    </section>

    {{-- Awards --}}
    <section class="section">
        <div class="container-x">
            <x-section-heading :eyebrow="__('site.about.awards_eyebrow')" :title="__('site.about.awards_title')" />
            <ul class="mt-12 grid gap-px overflow-hidden rounded-3xl border border-line bg-line sm:grid-cols-2 lg:grid-cols-3">
                @foreach (__('site.about.awards') as $award)
                    <li class="flex items-start gap-4 bg-surface p-7">
                        <x-heroicon-o-trophy class="size-8 shrink-0 text-gold-500" />
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wider text-muted">{{ $award['year'] }}</p>
                            <p class="mt-1 font-serif text-xl leading-snug">{{ $award['title'] }}</p>
                            <p class="mt-1 text-sm text-muted">{{ $award['by'] }}</p>
                        </div>
                    </li>
                @endforeach
            </ul>
        </div>
    </section>

    <section class="bg-midnight-950 py-16 text-white sm:py-20">
        <div class="container-x flex flex-col items-center justify-between gap-6 text-center md:flex-row md:text-left">
            <div>
                <h2 class="font-serif text-3xl sm:text-4xl">{{ __('site.about.cta_title') }}</h2>
                <p class="mt-2 text-white/70">{{ __('site.about.cta_text') }}</p>
            </div>
            <div class="flex shrink-0 gap-3">
                <a href="{{ route('rooms.index') }}" wire:navigate class="btn-ghost-light">{{ __('site.home.explore_rooms') }}</a>
                <a href="{{ route('booking') }}" class="btn-gold">{{ __('site.book_now') }}</a>
            </div>
        </div>
    </section>
</div>
