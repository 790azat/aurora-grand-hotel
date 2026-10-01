@props(['review', 'showRoom' => false])
<figure {{ $attributes->merge(['class' => 'card flex flex-col p-6 sm:p-7']) }}>
    <div class="flex items-center justify-between gap-3">
        <x-star-rating :rating="$review->rating" />
        <span class="text-xs text-muted">{{ $review->created_at?->translatedFormat('F Y') }}</span>
    </div>
    @if ($review->title)
        <h3 class="mt-4 font-serif text-xl leading-snug">“{{ $review->title }}”</h3>
    @endif
    <blockquote class="mt-3 flex-1 text-sm leading-relaxed text-muted">{{ $review->body }}</blockquote>
    @if ($review->reply)
        <div class="mt-4 rounded-xl border-l-2 border-gold-400 bg-elevated px-4 py-3 text-xs leading-relaxed text-muted">
            <p class="mb-1 font-semibold text-ink">{{ __('site.reviews.hotel_reply') }}</p>
            {{ $review->reply }}
        </div>
    @endif
    <figcaption class="mt-5 flex items-center gap-3 border-t border-line pt-4">
        <span class="grid size-10 shrink-0 place-items-center rounded-full bg-gold-100 font-serif text-lg font-semibold text-gold-800 dark:bg-gold-900/60 dark:text-gold-200">{{ mb_substr($review->name, 0, 1) }}</span>
        <span class="min-w-0">
            <span class="block truncate text-sm font-semibold">{{ $review->name }}</span>
            <span class="block truncate text-xs text-muted">
                {{ $review->country }}@if ($showRoom && $review->roomType) · {{ $review->roomType->name }}@endif
            </span>
        </span>
    </figcaption>
</figure>
