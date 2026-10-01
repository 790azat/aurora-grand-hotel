@props(['eyebrow' => null, 'title', 'subtitle' => null, 'align' => 'center', 'light' => false])
<div {{ $attributes->merge(['class' => ($align === 'center' ? 'mx-auto max-w-2xl text-center' : 'max-w-2xl')]) }}>
    @if ($eyebrow)
        <p class="eyebrow flex items-center gap-3 {{ $align === 'center' ? 'justify-center' : '' }}">
            <span class="h-px w-8 bg-gold-400/70"></span>{{ $eyebrow }}@if ($align === 'center')<span class="h-px w-8 bg-gold-400/70"></span>@endif
        </p>
    @endif
    <h2 class="h-section mt-4 {{ $light ? 'text-white' : '' }}">{{ $title }}</h2>
    @if ($subtitle)
        <p class="lead mt-5 {{ $light ? 'text-white/75' : '' }}">{{ $subtitle }}</p>
    @endif
    {{ $slot }}
</div>
