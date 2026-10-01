@props(['zoom' => 0.02])
@php
    $lat = (float) setting('map_lat');
    $lng = (float) setting('map_lng');
    $bbox = implode(',', [$lng - $zoom, $lat - $zoom / 2, $lng + $zoom, $lat + $zoom / 2]);
@endphp
<div {{ $attributes->merge(['class' => 'relative overflow-hidden rounded-3xl border border-line bg-elevated']) }}>
    <iframe title="{{ __('site.location.map_title') }}" loading="lazy" referrerpolicy="no-referrer-when-downgrade"
            src="https://www.openstreetmap.org/export/embed.html?bbox={{ $bbox }}&layer=mapnik&marker={{ $lat }},{{ $lng }}"
            class="absolute inset-0 size-full border-0 dark:opacity-85 dark:[filter:invert(0.9)_hue-rotate(180deg)_saturate(0.6)]"></iframe>
    <a href="https://www.openstreetmap.org/?mlat={{ $lat }}&mlon={{ $lng }}#map=15/{{ $lat }}/{{ $lng }}" target="_blank" rel="noopener"
       class="btn-dark btn-sm absolute right-4 bottom-4 shadow-lg">
        <x-heroicon-o-map-pin class="size-4" /> {{ __('site.location.open_map') }}
    </a>
</div>
