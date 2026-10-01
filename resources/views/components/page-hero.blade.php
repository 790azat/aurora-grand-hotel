@props(['title', 'eyebrow' => null, 'subtitle' => null, 'image' => null, 'size' => 'md'])
<section {{ $attributes->merge(['class' => 'relative isolate overflow-hidden bg-midnight-950 text-white']) }}>
    @if ($image)
        <img src="{{ $image }}" alt="" data-fallback="1" data-nofallback onerror="this.style.visibility='hidden'" class="absolute inset-0 -z-10 size-full object-cover opacity-60 animate-slow-zoom" fetchpriority="high">
    @endif
    <div class="absolute inset-0 -z-10 bg-gradient-to-b from-midnight-950/70 via-midnight-950/40 to-midnight-950/85"></div>
    <div class="container-x {{ $size === 'sm' ? 'py-16 sm:py-20' : 'py-20 sm:py-28 lg:py-32' }} text-center">
        @if ($eyebrow)
            <p class="eyebrow animate-fade-up text-gold-300">{{ $eyebrow }}</p>
        @endif
        <h1 class="h-display mx-auto mt-4 max-w-4xl animate-fade-up [animation-delay:80ms]">{{ $title }}</h1>
        @if ($subtitle)
            <p class="mx-auto mt-5 max-w-2xl text-base leading-relaxed text-white/75 animate-fade-up [animation-delay:160ms] sm:text-lg">{{ $subtitle }}</p>
        @endif
        {{ $slot }}
    </div>
</section>
