@php $b = $booking; @endphp
<div>
    @include('livewire.account.partials.header', ['heading' => __('account.booking.heading'), 'sub' => $b->reference])

    <div class="container-x py-10 sm:py-12">
        <a href="{{ route('account.dashboard') }}" wire:navigate class="inline-flex items-center gap-1.5 text-sm font-semibold text-muted hover:text-ink">
            @include('livewire.booking.partials.icon', ['name' => 'arrow-left', 'class' => 'size-4']) {{ __('account.booking.back') }}
        </a>

        <div class="mt-5 flex flex-wrap items-center gap-3">
            <span class="font-mono text-2xl font-semibold tracking-[0.12em] text-gold-600 dark:text-gold-300">{{ $b->reference }}</span>
            @include('livewire.booking.partials.status-badge', ['booking' => $b])
            <span class="text-xs text-muted">{{ __('account.booking.booked_on', ['date' => $b->created_at->translatedFormat('j M Y')]) }}</span>
        </div>

        <div class="mt-6">
            @include('livewire.booking.partials.manage-actions', ['booking' => $b])
        </div>

        <div class="mt-8">
            @include('livewire.booking.partials.details', ['booking' => $b])
        </div>

        {{-- Review --}}
        @if ($reviewSent)
            <div class="card mt-8 flex flex-col items-center p-8 text-center">
                <span class="grid size-14 place-items-center rounded-full bg-gold-100 text-gold-700 dark:bg-gold-900/50 dark:text-gold-200">@include('livewire.booking.partials.icon', ['name' => 'heart', 'class' => 'size-7'])</span>
                <h2 class="mt-4 font-serif text-3xl">{{ __('account.review.thanks_title') }}</h2>
                <p class="mt-2 max-w-md text-sm text-muted">{{ __('account.review.thanks_text') }}</p>
            </div>
        @elseif ($b->review)
            <div class="card mt-8 p-6">
                <p class="eyebrow">{{ __('account.review.yours') }}</p>
                <div class="mt-2 flex items-center gap-1 text-gold-500">
                    @for ($i = 1; $i <= 5; $i++)
                        @include('livewire.booking.partials.icon', ['name' => 'star', 'class' => 'size-5', 'solid' => $i <= $b->review->rating, 'stroke' => 1.4])
                    @endfor
                </div>
                @if ($b->review->title)<p class="mt-2 font-serif text-2xl">{{ $b->review->title }}</p>@endif
                <p class="mt-1 text-sm text-muted">{{ $b->review->body }}</p>
                <p class="mt-3 text-xs">
                    <span class="{{ $b->review->is_approved ? 'badge-green' : 'badge-gold' }}">{{ $b->review->is_approved ? __('account.review.published') : __('account.review.pending') }}</span>
                </p>
            </div>
        @elseif ($b->canBeReviewed())
            <div class="card mt-8 p-6 sm:p-8">
                <div class="grid gap-8 lg:grid-cols-3">
                    <div>
                        <p class="eyebrow">{{ __('account.review.eyebrow') }}</p>
                        <h2 class="mt-2 font-serif text-3xl">{{ __('account.review.title') }}</h2>
                        <p class="mt-2 text-sm text-muted">{{ __('account.review.text') }}</p>
                    </div>
                    <form wire:submit="submitReview" class="space-y-5 lg:col-span-2" novalidate>
                        <div x-data="{ hover: 0 }">
                            <span class="label">{{ __('account.review.rating') }}</span>
                            <div class="flex items-center gap-1" role="radiogroup" aria-label="{{ __('account.review.rating') }}" @mouseleave="hover = 0">
                                @for ($i = 1; $i <= 5; $i++)
                                    <button type="button" role="radio" aria-checked="{{ $rating === $i ? 'true' : 'false' }}" aria-label="{{ trans_choice('account.review.stars', $i) }}"
                                            wire:click="$set('rating', {{ $i }})" @mouseenter="hover = {{ $i }}"
                                            class="rounded p-0.5 transition hover:scale-110"
                                            :class="(hover ? hover >= {{ $i }} : {{ $rating >= $i ? 'true' : 'false' }}) ? 'text-gold-500' : 'text-line'">
                                        @include('livewire.booking.partials.icon', ['name' => 'star', 'class' => 'size-8', 'solid' => true, 'stroke' => 1])
                                    </button>
                                @endfor
                                <span class="ml-2 text-sm font-medium text-muted">{{ __('account.review.labels.'.$rating) }}</span>
                            </div>
                            @error('rating') <p class="input-error">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="review_title" class="label">{{ __('account.review.field_title') }}</label>
                            <input id="review_title" type="text" wire:model.blur="title" maxlength="120" placeholder="{{ __('account.review.title_placeholder') }}" class="input @error('title') border-rose-400 @enderror">
                            @error('title') <p class="input-error">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="review_body" class="label">{{ __('account.review.field_body') }}</label>
                            <textarea id="review_body" wire:model.blur="body" rows="5" maxlength="2000" placeholder="{{ __('account.review.body_placeholder') }}" class="input resize-y @error('body') border-rose-400 @enderror"></textarea>
                            @error('body') <p class="input-error">{{ $message }}</p> @enderror
                        </div>
                        <button type="submit" class="btn-gold" wire:loading.attr="disabled" wire:target="submitReview">
                            <span wire:loading.remove wire:target="submitReview">{{ __('account.review.submit') }}</span>
                            <span wire:loading wire:target="submitReview">{{ __('booking.please_wait') }}</span>
                        </button>
                    </form>
                </div>
            </div>
        @endif
    </div>
</div>
