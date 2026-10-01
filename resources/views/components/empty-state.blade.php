@props(['icon' => 'sparkles', 'title', 'text' => null])
<div {{ $attributes->merge(['class' => 'card flex flex-col items-center px-6 py-16 text-center']) }}>
    <span class="grid size-14 place-items-center rounded-full bg-gold-100 text-gold-700 dark:bg-gold-900/50 dark:text-gold-200"><x-hotel-icon :name="$icon" class="size-6" /></span>
    <h3 class="mt-5 font-serif text-2xl">{{ $title }}</h3>
    @if ($text)<p class="mt-2 max-w-md text-sm text-muted">{{ $text }}</p>@endif
    {{ $slot }}
</div>
