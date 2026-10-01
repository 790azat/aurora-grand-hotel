<x-filament-panels::page>
    <div class="ag-tape"
         x-data="{
            tip: null, x: 0, y: 0,
            show(e, data) { this.tip = data; this.move(e); },
            move(e) {
                const w = 260, pad = 14;
                this.x = Math.min(e.clientX + pad, window.innerWidth - w - 8);
                this.y = e.clientY + pad + 140 > window.innerHeight ? e.clientY - 140 : e.clientY + pad;
            },
            hide() { this.tip = null },
            scrollToday() {
                const el = this.$refs.scroller?.querySelector('.ag-tape-head .is-today');
                if (el) this.$refs.scroller.scrollLeft = Math.max(0, el.offsetLeft - 220);
            }
         }"
         style="--days: {{ $dates->count() }}">

        {{-- Toolbar --}}
        <div class="ag-tape-toolbar">
            <div class="ag-tape-nav">
                <x-filament::icon-button icon="heroicon-m-chevron-left" color="gray" wire:click="previous" :label="__('admin.tape.prev')" :tooltip="__('admin.tape.prev')" />
                <x-filament::button color="gray" size="sm" wire:click="goToday" icon="heroicon-m-calendar">{{ __('admin.tape.today') }}</x-filament::button>
                <x-filament::icon-button icon="heroicon-m-chevron-right" color="gray" wire:click="next" :label="__('admin.tape.next')" :tooltip="__('admin.tape.next')" />
                <span class="ag-tape-range">{{ $rangeLabel }}</span>
                <x-filament::loading-indicator class="ag-tape-spinner" wire:loading />
            </div>
            <div class="ag-tape-controls">
                <label class="ag-tape-field">
                    <span>{{ __('admin.tape.from') }}</span>
                    <input type="date" wire:model.live="start" class="ag-tape-input">
                </label>
                <label class="ag-tape-field">
                    <span>{{ __('admin.tape.days') }}</span>
                    <select wire:model.live="days" class="ag-tape-input">
                        @foreach (\App\Filament\Pages\TapeChart::DAY_OPTIONS as $opt)
                            <option value="{{ $opt }}">{{ $opt }}</option>
                        @endforeach
                    </select>
                </label>
            </div>
        </div>

        <div class="ag-tape-legend">
            @foreach (['pending', 'confirmed', 'checked_in', 'checked_out'] as $s)
                <span class="ag-legend-item"><i class="ag-swatch is-{{ $s }}"></i>{{ __('admin.status.'.$s) }}</span>
            @endforeach
            <span class="ag-legend-item"><i class="ag-swatch is-unpaid-dot"></i>{{ __('admin.tape.unpaid') }}</span>
            <span class="ag-legend-item"><i class="ag-swatch is-blocked"></i>{{ __('admin.tape.out_of_service') }}</span>
            @if ($canCreate)
                <span class="ag-legend-hint">{{ __('admin.tape.click_hint') }}</span>
            @endif
        </div>

        {{-- Grid --}}
        <div class="ag-tape-scroller" x-ref="scroller" wire:loading.class="is-loading">
            <div class="ag-tape-grid">
                {{-- Header --}}
                <div class="ag-tape-row ag-tape-head">
                    <div class="ag-tape-label ag-tape-corner">{{ __('admin.fields.room') }}</div>
                    <div class="ag-tape-track">
                        @foreach ($dates as $i => $d)
                            <div @class(['ag-tape-day', 'is-weekend' => $d->isWeekend(), 'is-today' => $d->isSameDay($today), 'is-month-start' => $d->day === 1 || $i === 0])>
                                @if ($d->day === 1 || $i === 0)<span class="ag-tape-month">{{ $d->isoFormat('MMM') }}</span>@endif
                                <span class="ag-tape-dow">{{ $d->isoFormat('dd') }}</span>
                                <span class="ag-tape-date">{{ $d->day }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
                <div class="ag-tape-row ag-tape-occ">
                    <div class="ag-tape-label">{{ __('admin.dashboard.occupancy') }}</div>
                    <div class="ag-tape-track">
                        @foreach ($occupancy as $i => $pct)
                            <div @class(['ag-tape-occ-cell', 'is-weekend' => $dates[$i]->isWeekend(), 'is-today' => $dates[$i]->isSameDay($today)]) title="{{ $pct }}%">
                                <span class="ag-occ-bar" style="height: {{ max(2, $pct) }}%"></span>
                                <span class="ag-occ-num">{{ $pct }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>

                @foreach ($types as $type)
                    <div class="ag-tape-row ag-tape-type">
                        <div class="ag-tape-label">
                            <span class="ag-type-name">{{ $type->name }}</span>
                            <span class="ag-type-count">{{ $type->rooms->count() }}</span>
                        </div>
                        <div class="ag-tape-track">
                            @foreach ($dates as $i => $d)
                                @php($f = $free[$type->id][$i] ?? 0)
                                <div @class(['ag-tape-free', 'is-weekend' => $d->isWeekend(), 'is-today' => $d->isSameDay($today), 'is-full' => $f === 0])
                                     title="{{ trans_choice('admin.booking.free_rooms', $f, ['count' => $f]) }}">{{ $f }}</div>
                            @endforeach
                        </div>
                    </div>

                    @foreach ($type->rooms as $room)
                        @php($blocked = $room->status !== 'available')
                        <div @class(['ag-tape-row ag-tape-room', 'is-blocked' => $blocked])>
                            <div class="ag-tape-label">
                                <span class="ag-room-no">{{ $room->number }}</span>
                                <span @class(['ag-hk', 'is-'.$room->housekeeping]) title="{{ __('admin.housekeeping.'.$room->housekeeping) }}"></span>
                                @if ($blocked)
                                    <span class="ag-room-flag" title="{{ $room->notes }}">{{ __('admin.room_status.'.$room->status) }}</span>
                                @else
                                    <span class="ag-room-floor">{{ __('admin.tape.floor_short', ['floor' => $room->floor]) }}</span>
                                @endif
                            </div>
                            <div class="ag-tape-track">
                                @foreach ($dates as $d)
                                    @if ($canCreate && ! $blocked)
                                        <a href="{{ $createUrl }}?room_type={{ $type->id }}&room={{ $room->id }}&check_in={{ $d->toDateString() }}"
                                           wire:navigate
                                           @class(['ag-tape-cell', 'is-weekend' => $d->isWeekend(), 'is-today' => $d->isSameDay($today), 'is-past' => $d->lt($today)])
                                           aria-label="{{ __('admin.tape.new_booking_for', ['room' => $room->number, 'date' => $d->isoFormat('D MMM')]) }}"></a>
                                    @else
                                        <div @class(['ag-tape-cell', 'is-weekend' => $d->isWeekend(), 'is-today' => $d->isSameDay($today), 'is-past' => $d->lt($today)])></div>
                                    @endif
                                @endforeach

                                @foreach ($byRoom->get($room->id, []) as $bar)
                                    <a href="{{ $bar['url'] }}" wire:navigate
                                       @class(['ag-bar', 'is-'.$bar['status'], 'cut-left' => $bar['cut_left'], 'cut-right' => $bar['cut_right']])
                                       style="left: calc(var(--cw) * {{ $bar['left'] }}); width: calc(var(--cw) * {{ $bar['width'] }} - 3px)"
                                       x-on:mouseenter="show($event, @js($bar))" x-on:mousemove="move($event)" x-on:mouseleave="hide()"
                                       aria-label="{{ $bar['ref'] }} · {{ $bar['guest'] }} · {{ $bar['dates'] }}">
                                        @if ($bar['unpaid'])<i class="ag-bar-dot"></i>@endif
                                        <span class="ag-bar-text">{{ $bar['guest'] }}</span>
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @endforeach

                    @if ($unassigned->has($type->id))
                        <div class="ag-tape-row ag-tape-room is-unassigned">
                            <div class="ag-tape-label"><span class="ag-room-flag">{{ __('admin.booking.unassigned') }}</span></div>
                            <div class="ag-tape-track">
                                @foreach ($dates as $d)
                                    <div @class(['ag-tape-cell', 'is-weekend' => $d->isWeekend(), 'is-today' => $d->isSameDay($today)])></div>
                                @endforeach
                                @foreach ($unassigned->get($type->id) as $bar)
                                    <a href="{{ $bar['url'] }}" wire:navigate @class(['ag-bar', 'is-'.$bar['status']])
                                       style="left: calc(var(--cw) * {{ $bar['left'] }}); width: calc(var(--cw) * {{ $bar['width'] }} - 3px)"
                                       x-on:mouseenter="show($event, @js($bar))" x-on:mousemove="move($event)" x-on:mouseleave="hide()">
                                        <span class="ag-bar-text">{{ $bar['guest'] }}</span>
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @endif
                @endforeach
            </div>
        </div>

        {{-- Tooltip --}}
        <template x-teleport="body">
            <div class="ag-tip" x-show="tip" x-cloak x-bind:style="`left:${x}px;top:${y}px`">
                <template x-if="tip">
                    <div>
                        <div class="ag-tip-head">
                            <span class="ag-tip-ref" x-text="tip.ref"></span>
                            <span class="ag-tip-status" x-bind:class="'is-' + tip.status" x-text="@js(collect(\App\Models\Booking::STATUSES)->mapWithKeys(fn ($s) => [$s => __('admin.status.'.$s)]))[tip.status]"></span>
                        </div>
                        <div class="ag-tip-guest" x-text="tip.guest"></div>
                        <div class="ag-tip-row"><span>{{ __('admin.fields.dates') }}</span><b x-text="tip.dates"></b></div>
                        <div class="ag-tip-row"><span>{{ __('admin.fields.nights') }} / {{ __('admin.fields.guests') }}</span><b x-text="tip.nights + ' / ' + tip.guests"></b></div>
                        <div class="ag-tip-row"><span>{{ __('admin.fields.total') }}</span><b x-text="tip.total"></b></div>
                        <div class="ag-tip-unpaid" x-show="tip.unpaid">{{ __('admin.tape.balance_due') }}</div>
                    </div>
                </template>
            </div>
        </template>
    </div>
</x-filament-panels::page>
