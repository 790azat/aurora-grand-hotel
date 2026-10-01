@props(['doc'])
@php $sections = __("site.legal.$doc.sections"); @endphp
<div>
    <x-page-hero size="sm" :eyebrow="__('site.legal.eyebrow')" :title="__('site.legal.'.$doc.'.title')"
                 :subtitle="__('site.legal.updated', ['date' => \Carbon\Carbon::parse('2026-09-01')->translatedFormat('j F Y')])" />
    <div class="container-x section grid max-w-6xl gap-12 lg:grid-cols-[240px_1fr]">
        <nav class="hidden lg:block" aria-label="{{ __('site.legal.contents') }}">
            <div class="sticky top-24">
                <p class="label">{{ __('site.legal.contents') }}</p>
                <ol class="mt-3 space-y-2 border-l border-line text-sm">
                    @foreach ($sections as $i => $section)
                        <li><a href="#s{{ $i + 1 }}" class="-ml-px block border-l border-transparent pl-4 text-muted transition hover:border-gold-500 hover:text-ink">{{ $section['h'] }}</a></li>
                    @endforeach
                </ol>
            </div>
        </nav>
        <div class="min-w-0 max-w-3xl">
            <p class="lead">{{ __("site.legal.$doc.intro") }}</p>
            @foreach ($sections as $i => $section)
                <section id="s{{ $i + 1 }}" class="mt-10 scroll-mt-24">
                    <h2 class="font-serif text-3xl"><span class="mr-2 text-gold-500">{{ $i + 1 }}.</span>{{ $section['h'] }}</h2>
                    @foreach ((array) $section['p'] as $paragraph)
                        <p class="mt-4 leading-relaxed text-muted">{{ $paragraph }}</p>
                    @endforeach
                    @if (! empty($section['list']))
                        <ul class="mt-4 list-disc space-y-1.5 pl-5 text-muted marker:text-gold-500">
                            @foreach ($section['list'] as $li)<li>{{ $li }}</li>@endforeach
                        </ul>
                    @endif
                </section>
            @endforeach
            <div class="mt-14 rounded-2xl bg-elevated p-6 text-sm text-muted">
                {{ __('site.legal.questions') }}
                <a href="mailto:{{ setting('hotel_email') }}" class="link">{{ setting('hotel_email') }}</a>
            </div>
        </div>
    </div>
</div>
