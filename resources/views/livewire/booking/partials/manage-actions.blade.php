{{-- Guest actions for a booking: pay balance, invoice, cancel (policy-aware, confirm modal). Expects $booking. --}}
@php
    $b = $booking;
    $canCancel = $b->canBeCancelledByGuest();
    $canPay = $b->status !== 'cancelled' && $b->payment_method === 'card' && $b->balance > 0 && in_array($b->status, ['pending', 'confirmed'], true);
    $deadline = $b->check_in->copy()->setTime(14, 0)->subHours((int) setting('free_cancellation_hours'));
@endphp
<div x-data="{ confirmCancel: false }" @keydown.escape.window="confirmCancel = false" @booking-cancelled.window="confirmCancel = false">
    <div class="flex flex-wrap gap-3">
        @if ($canPay)
            <a href="{{ route('booking.pay', $b) }}" class="btn-gold">@include('livewire.booking.partials.icon', ['name' => 'credit-card', 'class' => 'size-4']) {{ __('booking.pay_now_amount', ['amount' => money($b->balance, true)]) }}</a>
        @endif
        <a href="{{ route('booking.invoice', $b) }}" class="btn-dark">@include('livewire.booking.partials.icon', ['name' => 'download', 'class' => 'size-4']) {{ __('booking.download_invoice') }}</a>
        <a href="{{ route('booking.confirmation', $b) }}" class="btn-outline">@include('livewire.booking.partials.icon', ['name' => 'document', 'class' => 'size-4']) {{ __('booking.view_confirmation') }}</a>
        @if ($canCancel)
            <button type="button" @click="confirmCancel = true" class="btn border border-rose-300 text-rose-700 hover:bg-rose-50 dark:border-rose-800 dark:text-rose-300 dark:hover:bg-rose-950/40">
                @include('livewire.booking.partials.icon', ['name' => 'x', 'class' => 'size-4']) {{ __('booking.cancel.button') }}
            </button>
        @endif
    </div>

    {{-- Policy --}}
    <div class="mt-5 flex items-start gap-3 rounded-2xl bg-elevated/70 p-4 text-sm">
        @include('livewire.booking.partials.icon', ['name' => 'shield', 'class' => 'size-5 shrink-0 text-gold-500'])
        <div class="text-muted">
            <p class="font-semibold text-ink">{{ __('booking.cancel.policy_title') }}</p>
            <p class="mt-0.5">{{ __('booking.cancellation_policy', ['hours' => setting('free_cancellation_hours')]) }}</p>
            @if ($b->status === 'cancelled')
                <p class="mt-1">{{ __('booking.cancel.already', ['date' => $b->cancelled_at?->translatedFormat('j M Y, H:i') ?? '—']) }}</p>
            @elseif ($canCancel)
                <p class="mt-1 font-medium text-emerald-700 dark:text-emerald-400">{{ __('booking.cancel.free_until', ['date' => $deadline->translatedFormat('j M Y, H:i')]) }}</p>
            @elseif (in_array($b->status, ['pending', 'confirmed'], true))
                <p class="mt-1 font-medium text-gold-700 dark:text-gold-300">{{ __('booking.cancel.window_passed', ['phone' => setting('hotel_phone')]) }}</p>
            @endif
        </div>
    </div>

    {{-- Confirm modal --}}
    <template x-teleport="body">
        <div x-show="confirmCancel" x-cloak class="fixed inset-0 z-[70] flex items-end justify-center p-4 sm:items-center" role="dialog" aria-modal="true" aria-labelledby="cancel-title">
            <div x-show="confirmCancel" x-transition.opacity class="absolute inset-0 bg-midnight-950/70 backdrop-blur-sm" @click="confirmCancel = false"></div>
            <div x-show="confirmCancel" x-transition x-trap.noscroll="confirmCancel" class="card relative w-full max-w-md p-6 shadow-2xl">
                <div class="grid size-12 place-items-center rounded-full bg-rose-100 text-rose-600 dark:bg-rose-950/60 dark:text-rose-300">
                    @include('livewire.booking.partials.icon', ['name' => 'warning', 'class' => 'size-6'])
                </div>
                <h3 id="cancel-title" class="mt-4 font-serif text-2xl">{{ __('booking.cancel.confirm_title') }}</h3>
                <p class="mt-2 text-sm text-muted">{{ __('booking.cancel.confirm_text', ['reference' => $b->reference]) }}</p>
                @if ((float) $b->amount_paid > 0)
                    <p class="mt-3 rounded-xl bg-emerald-50 p-3 text-sm text-emerald-800 dark:bg-emerald-950/40 dark:text-emerald-200">{{ __('booking.cancel.refund_note', ['amount' => money($b->amount_paid, true)]) }}</p>
                @endif
                <div class="mt-6 flex flex-col-reverse gap-2 sm:flex-row sm:justify-end">
                    <button type="button" class="btn-outline" @click="confirmCancel = false">{{ __('booking.cancel.keep') }}</button>
                    <button type="button" class="btn bg-rose-600 text-white hover:bg-rose-700" wire:click="cancel" wire:loading.attr="disabled" wire:target="cancel">
                        <span wire:loading.remove wire:target="cancel">{{ __('booking.cancel.confirm') }}</span>
                        <span wire:loading wire:target="cancel">{{ __('booking.please_wait') }}</span>
                    </button>
                </div>
            </div>
        </div>
    </template>
</div>
