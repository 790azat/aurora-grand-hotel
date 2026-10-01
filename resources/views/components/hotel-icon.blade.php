@props(['name' => 'sparkles', 'variant' => 'o'])
@php
    // Heroicon by name (as stored in DB `icon` columns), falling back to "sparkles" if unknown.
    $icon = $name ?: 'sparkles';
    if (! is_file(base_path("vendor/blade-ui-kit/blade-heroicons/resources/svg/{$variant}-{$icon}.svg"))) {
        $icon = 'sparkles';
    }
@endphp
<x-dynamic-component :component="'heroicon-'.$variant.'-'.$icon" {{ $attributes->merge(['aria-hidden' => 'true']) }} />
