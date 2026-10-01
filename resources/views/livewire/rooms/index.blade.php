<div>
    <x-page-hero :eyebrow="__('site.rooms.eyebrow')" :title="__('site.rooms.title')" :subtitle="__('site.rooms.subtitle')"
                 image="https://images.unsplash.com/photo-1582719478250-c89cae4dc85b?auto=format&fit=crop&w=2000&q=80" />

    {{-- Filters --}}
    <section class="sticky top-18 z-30 border-b border-line bg-page/90 backdrop-blur-xl" aria-label="{{ __('site.rooms.filters') }}">
        <div class="container-x py-4" x-data="{ more: false }">
            <div class="flex items-center justify-between gap-3 lg:hidden">
                <p class="text-sm font-semibold">{{ __('site.rooms.filters') }}</p>
                <button type="button" @click="more = !more" class="btn-outline btn-sm" :aria-expanded="more">
                    <x-heroicon-o-adjustments-horizontal class="size-4" /> <span x-text="more ? @js(__('site.rooms.hide_filters')) : @js(__('site.rooms.show_filters'))"></span>
                </button>
            </div>
            <div :class="more && '!grid'" class="hidden mt-4 gap-3 sm:grid-cols-2 lg:mt-0 lg:grid lg:grid-cols-[1fr_1fr_0.6fr_0.6fr_0.9fr_1fr] lg:items-end">
                <label class="block">
                    <span class="label">{{ __('site.search.check_in') }}</span>
                    <input type="date" wire:model.live="check_in" min="{{ today()->toDateString() }}" class="input py-2.5">
                </label>
                <label class="block">
                    <span class="label">{{ __('site.search.check_out') }}</span>
                    <input type="date" wire:model.live="check_out" min="{{ $check_in ? \Carbon\Carbon::parse($check_in)->addDay()->toDateString() : today()->addDay()->toDateString() }}" class="input py-2.5">
                </label>
                <label class="block">
                    <span class="label">{{ __('site.search.adults') }}</span>
                    <select wire:model.live="adults" class="input py-2.5">
                        @for ($i = 1; $i <= 6; $i++)<option value="{{ $i }}">{{ $i }}</option>@endfor
                    </select>
                </label>
                <label class="block">
                    <span class="label">{{ __('site.search.children') }}</span>
                    <select wire:model.live="children" class="input py-2.5">
                        @for ($i = 0; $i <= 4; $i++)<option value="{{ $i }}">{{ $i }}</option>@endfor
                    </select>
                </label>
                <label class="block">
                    <span class="label">{{ __('site.rooms.price_per_night') }}</span>
                    <select wire:model.live="max_price" class="input py-2.5">
                        @foreach (\App\Livewire\Rooms\Index::PRICE_STEPS as $step)
                            <option value="{{ $step }}">{{ $step ? __('site.rooms.up_to', ['price' => money($step)]) : __('site.rooms.any_price') }}</option>
                        @endforeach
                    </select>
                </label>
                <label class="block">
                    <span class="label">{{ __('site.rooms.sort_by') }}</span>
                    <select wire:model.live="sort" class="input py-2.5">
                        @foreach (\App\Livewire\Rooms\Index::SORTS as $option)
                            <option value="{{ $option }}">{{ __('site.rooms.sort.'.$option) }}</option>
                        @endforeach
                    </select>
                </label>
            </div>
        </div>
    </section>

    <section class="section pt-10 sm:pt-12">
        <div class="container-x">
            {{-- Summary --}}
            <div class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex items-center gap-3 text-sm" aria-live="polite">
                    <span wire:loading.delay class="size-4 animate-spin rounded-full border-2 border-gold-400 border-t-transparent"></span>
                    @if ($hasDates)
                        <p>
                            <span class="font-semibold">{{ \Carbon\Carbon::parse($check_in)->translatedFormat('j M') }} – {{ \Carbon\Carbon::parse($check_out)->translatedFormat('j M Y') }}</span>
                            <span class="text-muted">· {{ trans_choice('site.rooms.nights', $nights, ['count' => $nights]) }} ·</span>
                            @if ($availableTypes)
                                <span class="font-semibold text-emerald-700 dark:text-emerald-400">{{ trans_choice('site.rooms.available_summary', $availableRooms, ['count' => $availableRooms, 'types' => $availableTypes]) }}</span>
                            @else
                                <span class="font-semibold text-rose-600 dark:text-rose-400">{{ __('site.rooms.nothing_available') }}</span>
                            @endif
                        </p>
                    @else
                        <p class="text-muted">{{ trans_choice('site.rooms.showing', $results->count(), ['count' => $results->count()]) }} · {{ __('site.rooms.add_dates_hint') }}</p>
                    @endif
                </div>
                <div class="flex gap-2">
                    @if ($hasDates)
                        <button type="button" wire:click="clearDates" class="btn-outline btn-sm"><x-heroicon-o-x-mark class="size-4" /> {{ __('site.rooms.clear_dates') }}</button>
                    @endif
                    @if ($hasDates || $max_price || $sort !== 'recommended' || $adults !== 2 || $children)
                        <button type="button" wire:click="resetFilters" class="btn-outline btn-sm"><x-heroicon-o-arrow-path class="size-4" /> {{ __('site.rooms.reset') }}</button>
                    @endif
                </div>
            </div>

            <div class="relative">
                <div wire:loading.delay.class="opacity-50 pointer-events-none" class="grid gap-8 transition-opacity md:grid-cols-2 lg:grid-cols-3">
                    @forelse ($results as $result)
                        <x-room-card wire:key="type-{{ $result['type']->id }}" :type="$result['type']" :quote="$result['quote']" :available="$result['available']" :params="$params" />
                    @empty
                        <x-empty-state class="md:col-span-2 lg:col-span-3" icon="magnifying-glass" :title="__('site.rooms.empty_title')" :text="__('site.rooms.empty_text')">
                            <button type="button" wire:click="resetFilters" class="btn-gold mt-6">{{ __('site.rooms.reset') }}</button>
                        </x-empty-state>
                    @endforelse
                </div>
            </div>

            {{-- Reassurance --}}
            <div class="mt-16 grid gap-4 rounded-3xl bg-elevated p-6 sm:grid-cols-3 sm:p-8">
                @foreach (__('site.rooms.perks') as $perk)
                    <div class="flex items-start gap-4">
                        <span class="grid size-11 shrink-0 place-items-center rounded-full bg-surface text-gold-600 dark:text-gold-300"><x-hotel-icon :name="$perk['icon']" class="size-5" /></span>
                        <div>
                            <p class="font-semibold">{{ $perk['title'] }}</p>
                            <p class="mt-1 text-sm text-muted">{{ $perk['text'] }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>
</div>
