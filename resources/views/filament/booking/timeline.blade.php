@php
    $b = $getRecord();
    $events = collect([
        ['created', $b->created_at, 'heroicon-m-plus-circle', 'gray'],
        ['confirmed', $b->confirmed_at, 'heroicon-m-check-badge', 'info'],
        ['checked_in', $b->checked_in_at, 'heroicon-m-key', 'success'],
        ['checked_out', $b->checked_out_at, 'heroicon-m-arrow-right-start-on-rectangle', 'gray'],
        ['cancelled', $b->cancelled_at, 'heroicon-m-x-circle', 'danger'],
    ]);
    foreach ($b->payments as $p) {
        $events->push(['payment', $p->created_at, 'heroicon-m-banknotes', $p->status === 'succeeded' ? 'success' : 'danger', $p]);
    }
    $events = $events->filter(fn ($e) => $e[1])->sortBy(fn ($e) => $e[1]->timestamp)->values();
@endphp
<ol class="ag-timeline">
    @foreach ($events as $e)
        <li class="ag-timeline-item is-{{ $e[3] }}">
            <span class="ag-timeline-dot"><x-filament::icon :icon="$e[2]" /></span>
            <div>
                <p class="ag-timeline-title">
                    @if ($e[0] === 'payment')
                        {{ __('admin.timeline.payment', ['amount' => money($e[4]->amount, true), 'method' => __('admin.payment_method.'.$e[4]->method)]) }}
                        @if ($e[4]->status !== 'succeeded') · {{ __('admin.payment_result.'.$e[4]->status) }} @endif
                    @else
                        {{ __('admin.timeline.'.$e[0]) }}
                    @endif
                </p>
                <p class="ag-timeline-time" title="{{ $e[1]->format('Y-m-d H:i') }}">{{ $e[1]->isoFormat('D MMM YYYY, HH:mm') }}</p>
            </div>
        </li>
    @endforeach
</ol>
