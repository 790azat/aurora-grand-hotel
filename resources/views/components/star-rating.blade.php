@props(['rating' => 5, 'size' => 'size-4'])
@php $rating = (float) $rating; @endphp
<div {{ $attributes->merge(['class' => 'inline-flex items-center gap-0.5']) }} role="img" aria-label="{{ __('site.common.rating_of', ['rating' => number_format($rating, 1)]) }}">
    @for ($i = 1; $i <= 5; $i++)
        @php $fill = max(0, min(1, $rating - $i + 1)); @endphp
        <span class="relative inline-block {{ $size }}">
            <svg class="absolute inset-0 {{ $size }} text-gold-200 dark:text-gold-900" viewBox="0 0 20 20" fill="currentColor"><path d="M10.868 2.884c-.321-.772-1.415-.772-1.736 0l-1.83 4.401-4.753.381c-.833.067-1.171 1.107-.536 1.651l3.62 3.102-1.106 4.637c-.194.813.691 1.456 1.405 1.02L10 15.591l4.069 2.485c.713.436 1.598-.207 1.404-1.02l-1.106-4.637 3.62-3.102c.635-.544.297-1.584-.536-1.65l-4.752-.382-1.831-4.401Z"/></svg>
            @if ($fill > 0)
                <span class="absolute inset-0 overflow-hidden" style="width: {{ $fill * 100 }}%">
                    <svg class="{{ $size }} text-gold-400" viewBox="0 0 20 20" fill="currentColor"><path d="M10.868 2.884c-.321-.772-1.415-.772-1.736 0l-1.83 4.401-4.753.381c-.833.067-1.171 1.107-.536 1.651l3.62 3.102-1.106 4.637c-.194.813.691 1.456 1.405 1.02L10 15.591l4.069 2.485c.713.436 1.598-.207 1.404-1.02l-1.106-4.637 3.62-3.102c.635-.544.297-1.584-.536-1.65l-4.752-.382-1.831-4.401Z"/></svg>
                </span>
            @endif
        </span>
    @endfor
</div>
