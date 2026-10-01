@props([
    'checkIn' => null,
    'checkOut' => null,
    'adults' => 2,
    'children' => 0,
    'roomType' => null,
    'variant' => 'hero', // hero (glass on dark image) | card
])
@php
    $checkIn = $checkIn ?: now()->addDay()->toDateString();
    $checkOut = $checkOut ?: \Carbon\Carbon::parse($checkIn)->addDays(3)->toDateString();
    $glass = $variant === 'hero';
    $fieldLabel = $glass ? 'text-[11px] font-semibold uppercase tracking-[0.2em] text-gold-200' : 'label';
    $fieldInput = $glass
        ? 'block w-full border-0 bg-transparent p-0 pt-1 text-base font-medium text-white [color-scheme:dark] focus:ring-0 focus:outline-none'
        : 'input';
@endphp
<form method="GET" action="{{ route('booking') }}"
      x-data="{
          checkIn: @js($checkIn),
          checkOut: @js($checkOut),
          error: '',
          fmt(x) { return x.getFullYear() + '-' + String(x.getMonth() + 1).padStart(2, '0') + '-' + String(x.getDate()).padStart(2, '0') },
          get today() { return this.fmt(new Date()) },
          addDays(d, n) { const x = new Date(d + 'T00:00:00'); x.setDate(x.getDate() + n); return this.fmt(x) },
          syncOut() { if (this.checkIn && (!this.checkOut || this.checkOut <= this.checkIn)) this.checkOut = this.addDays(this.checkIn, 1); this.error = '' },
          submit(e) {
              if (!this.checkIn || !this.checkOut) { this.error = @js(__('site.search.error_dates')); e.preventDefault(); return }
              if (this.checkOut <= this.checkIn) { this.error = @js(__('site.search.error_order')); e.preventDefault() }
          }
      }"
      @submit="submit($event)"
      {{ $attributes->merge(['class' => $glass
          ? 'rounded-3xl border border-white/15 bg-midnight-950/55 p-3 shadow-2xl backdrop-blur-xl'
          : 'card p-5']) }}>
    @if ($roomType)
        <input type="hidden" name="room_type" value="{{ $roomType }}">
    @endif
    <div class="grid gap-2 {{ $glass ? 'sm:grid-cols-2 lg:grid-cols-[1fr_1fr_0.7fr_0.7fr_auto]' : 'gap-4 sm:grid-cols-2 lg:grid-cols-[1fr_1fr_0.7fr_0.7fr_auto] lg:items-end' }}">
        <label class="{{ $glass ? 'block rounded-2xl px-4 py-3 transition hover:bg-white/5 focus-within:bg-white/10' : 'block' }}">
            <span class="{{ $fieldLabel }}">{{ __('site.search.check_in') }}</span>
            <input type="date" name="check_in" x-model="checkIn" :min="today" @change="syncOut()" required class="{{ $fieldInput }}">
        </label>
        <label class="{{ $glass ? 'block rounded-2xl px-4 py-3 transition hover:bg-white/5 focus-within:bg-white/10 lg:border-l lg:border-white/10' : 'block' }}">
            <span class="{{ $fieldLabel }}">{{ __('site.search.check_out') }}</span>
            <input type="date" name="check_out" x-model="checkOut" :min="checkIn ? addDays(checkIn, 1) : today" @change="error = ''" required class="{{ $fieldInput }}">
        </label>
        <label class="{{ $glass ? 'block rounded-2xl px-4 py-3 transition hover:bg-white/5 focus-within:bg-white/10 lg:border-l lg:border-white/10' : 'block' }}">
            <span class="{{ $fieldLabel }}">{{ __('site.search.adults') }}</span>
            <select name="adults" class="{{ $fieldInput }} {{ $glass ? '[&>option]:text-ink' : '' }}">
                @for ($i = 1; $i <= 6; $i++)
                    <option value="{{ $i }}" @selected($i == $adults)>{{ $i }}</option>
                @endfor
            </select>
        </label>
        <label class="{{ $glass ? 'block rounded-2xl px-4 py-3 transition hover:bg-white/5 focus-within:bg-white/10 lg:border-l lg:border-white/10' : 'block' }}">
            <span class="{{ $fieldLabel }}">{{ __('site.search.children') }}</span>
            <select name="children" class="{{ $fieldInput }} {{ $glass ? '[&>option]:text-ink' : '' }}">
                @for ($i = 0; $i <= 4; $i++)
                    <option value="{{ $i }}" @selected($i == $children)>{{ $i }}</option>
                @endfor
            </select>
        </label>
        <button type="submit" class="btn-gold {{ $glass ? 'h-full min-h-14 rounded-2xl px-8 sm:col-span-2 lg:col-span-1' : 'sm:col-span-2 lg:col-span-1' }}">
            <x-heroicon-o-magnifying-glass class="size-4" />
            {{ __('site.search.submit') }}
        </button>
    </div>
    <p x-show="error" x-cloak x-text="error" role="alert" class="{{ $glass ? 'px-4 pt-2 text-sm text-rose-300' : 'input-error' }}"></p>
</form>
