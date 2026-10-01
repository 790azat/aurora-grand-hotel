@php
    $b = $booking;
    $d = fn ($date, $f = 'j M Y') => \Illuminate\Support\Carbon::parse($date)->translatedFormat($f);
    $m = fn ($v) => money($v, true);
    $stamp = match (true) {
        $b->status === 'cancelled' => ['text' => __('booking.invoice.stamp_cancelled'), 'color' => '#b42318'],
        $b->payment_status === 'refunded' => ['text' => __('booking.invoice.stamp_refunded'), 'color' => '#6b665c'],
        $b->balance <= 0 => ['text' => __('booking.invoice.stamp_paid'), 'color' => '#067647'],
        default => ['text' => __('booking.invoice.stamp_due'), 'color' => '#9a763a'],
    };
    $countryLabel = __('booking.countries')[$b->country] ?? $b->country;
    $taxPercent = (float) setting('tax_percent', 10);
@endphp
<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <title>{{ __('booking.invoice.title') }} {{ $b->reference }}</title>
    <style>
        @page { margin: 0; }
        * { box-sizing: border-box; }
        body { margin: 0; font-family: 'DejaVu Sans', sans-serif; font-size: 10px; color: #17150f; line-height: 1.45; }
        .serif { font-family: 'DejaVu Serif', serif; }
        .band { background: #0b1118; color: #fff; padding: 34px 44px 28px; }
        .gold { color: #cba65a; }
        .muted { color: #6b665c; }
        .wrap { padding: 26px 44px 0; }
        table { width: 100%; border-collapse: collapse; }
        td, th { vertical-align: top; }
        .brand { font-size: 22px; letter-spacing: 5px; }
        .brand-sub { font-size: 7px; letter-spacing: 4px; color: #cba65a; }
        .h-inv { font-size: 24px; letter-spacing: 3px; text-align: right; }
        .meta td { padding: 1px 0; font-size: 9px; }
        .label { font-size: 7.5px; letter-spacing: 1.5px; text-transform: uppercase; color: #9a763a; font-weight: bold; margin-bottom: 5px; }
        .box { border: 1px solid #e6e0d3; border-radius: 6px; padding: 12px 14px; }
        .items th { font-size: 7.5px; letter-spacing: 1.2px; text-transform: uppercase; color: #6b665c; text-align: left; padding: 8px 8px; border-bottom: 1.5px solid #17150f; }
        .items td { padding: 8px 8px; border-bottom: 1px solid #ece6da; }
        .num, .items th.num, .items td.num { text-align: right; white-space: nowrap; }
        .totals td { padding: 4px 8px; }
        .totals .grand td { border-top: 1.5px solid #17150f; padding-top: 8px; font-size: 13px; font-weight: bold; }
        .stamp { display: inline-block; border: 2.5px solid; border-radius: 6px; padding: 5px 12px; font-size: 13px; font-weight: bold; letter-spacing: 3px; text-transform: uppercase; transform: rotate(-8deg); }
        .footer { position: fixed; bottom: 0; left: 0; right: 0; padding: 14px 44px; border-top: 1px solid #e6e0d3; font-size: 8px; color: #6b665c; }
        .small { font-size: 8.5px; }
    </style>
</head>
<body>
    <div class="band">
        <table>
            <tr>
                <td style="width: 55%;">
                    <div class="serif brand">AURORA</div>
                    <div class="brand-sub">GRAND HOTEL &amp; SPA · ★★★★★</div>
                    <div style="margin-top: 12px; font-size: 8.5px; color: #c9c4b8;">
                        {{ \App\Models\Setting::localized('hotel_address') }}<br>
                        {{ setting('hotel_phone') }} · {{ setting('hotel_email') }}
                    </div>
                </td>
                <td style="width: 45%;">
                    <div class="serif h-inv gold">{{ mb_strtoupper(__('booking.invoice.title')) }}</div>
                    <table class="meta" style="margin-top: 8px; color: #e6e0d3;">
                        <tr><td style="text-align: right; color: #9a978f;">{{ __('booking.invoice.number') }}</td><td style="text-align: right; width: 42%;"><strong>{{ $b->reference }}</strong></td></tr>
                        <tr><td style="text-align: right; color: #9a978f;">{{ __('booking.invoice.date') }}</td><td style="text-align: right;">{{ $d($b->created_at) }}</td></tr>
                        <tr><td style="text-align: right; color: #9a978f;">{{ __('booking.invoice.status') }}</td><td style="text-align: right;">{{ __('booking.status.'.$b->status) }}</td></tr>
                    </table>
                </td>
            </tr>
        </table>
    </div>

    <div class="wrap">
        <table>
            <tr>
                <td style="width: 48%; padding-right: 12px;">
                    <div class="box">
                        <div class="label">{{ __('booking.invoice.bill_to') }}</div>
                        <div style="font-size: 11px; font-weight: bold;">{{ $b->guest_name }}</div>
                        <div class="muted">{{ $b->email }}</div>
                        @if ($b->phone)<div class="muted">{{ $b->phone }}</div>@endif
                        @if ($countryLabel)<div class="muted">{{ $countryLabel }}</div>@endif
                    </div>
                </td>
                <td style="width: 52%;">
                    <div class="box">
                        <div class="label">{{ __('booking.invoice.stay') }}</div>
                        <table class="small">
                            <tr><td class="muted" style="width: 40%;">{{ __('booking.invoice.room') }}</td><td><strong>{{ $b->roomType->name }}</strong>@if ($b->room) · №{{ $b->room->number }}@endif</td></tr>
                            <tr><td class="muted">{{ __('booking.check_in') }}</td><td>{{ $d($b->check_in, 'D, j M Y') }}, {{ setting('check_in_time') }}</td></tr>
                            <tr><td class="muted">{{ __('booking.check_out') }}</td><td>{{ $d($b->check_out, 'D, j M Y') }}, {{ setting('check_out_time') }}</td></tr>
                            <tr><td class="muted">{{ __('booking.guests') }}</td><td>{{ trans_choice('booking.adults_count', $b->adults) }}@if ($b->children), {{ trans_choice('booking.children_count', $b->children) }}@endif · {{ trans_choice('booking.nights_count', $b->nights) }}</td></tr>
                        </table>
                    </div>
                </td>
            </tr>
        </table>

        <table class="items" style="margin-top: 22px;">
            <thead>
                <tr>
                    <th style="width: 56%;">{{ __('booking.invoice.description') }}</th>
                    <th class="num" style="width: 10%;">{{ __('booking.invoice.qty') }}</th>
                    <th class="num" style="width: 16%;">{{ __('booking.invoice.unit_price') }}</th>
                    <th class="num" style="width: 18%;">{{ __('booking.invoice.amount') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($nightGroups as $g)
                    <tr>
                        <td>
                            <strong>{{ $b->roomType->name }}</strong> — {{ trans_choice('booking.nights_count', $g['nights']) }}<br>
                            <span class="muted small">{{ $d($g['from'], 'j M') }} — {{ $d(\Illuminate\Support\Carbon::parse($g['to'])->addDay(), 'j M Y') }}</span>
                        </td>
                        <td class="num">{{ $g['nights'] }}</td>
                        <td class="num">{{ $m($g['price']) }}</td>
                        <td class="num">{{ $m($g['total']) }}</td>
                    </tr>
                @endforeach
                @foreach ($b->extras as $extra)
                    <tr>
                        <td>{{ $extra->name }}<br><span class="muted small">{{ __('booking.pricing.'.$extra->pricing) }}</span></td>
                        <td class="num">{{ $extra->pivot->quantity }}</td>
                        <td class="num">{{ $m($extra->pivot->unit_price) }}</td>
                        <td class="num">{{ $m($extra->pivot->total) }}</td>
                    </tr>
                @endforeach
                @if ((float) $b->discount > 0)
                    <tr>
                        <td style="color: #067647;">{{ __('booking.discount') }}@if ($b->promoCode) — {{ __('booking.invoice.promo', ['code' => $b->promoCode->code]) }}@endif</td>
                        <td class="num"></td>
                        <td class="num"></td>
                        <td class="num" style="color: #067647;">−{{ $m($b->discount) }}</td>
                    </tr>
                @endif
            </tbody>
        </table>

        <table style="margin-top: 14px;">
            <tr>
                <td style="width: 50%; padding-top: 18px; text-align: center;">
                    <span class="stamp" style="color: {{ $stamp['color'] }}; border-color: {{ $stamp['color'] }};">{{ $stamp['text'] }}</span>
                </td>
                <td style="width: 50%;">
                    <table class="totals">
                        <tr><td class="muted">{{ __('booking.invoice.subtotal') }}</td><td class="num">{{ $m((float) $b->room_total + (float) $b->extras_total - (float) $b->discount) }}</td></tr>
                        <tr><td class="muted">{{ __('booking.summary.tax', ['percent' => rtrim(rtrim(number_format($taxPercent, 2), '0'), '.')]) }}</td><td class="num">{{ $m($b->tax) }}</td></tr>
                        <tr class="grand"><td>{{ __('booking.summary.total') }} ({{ setting('currency') }})</td><td class="num">{{ $m($b->total) }}</td></tr>
                        <tr><td class="muted">{{ __('booking.paid') }}</td><td class="num">{{ $m($b->amount_paid) }}</td></tr>
                        @if ($b->status !== 'cancelled')
                            <tr><td><strong>{{ __('booking.balance_due') }}</strong></td><td class="num"><strong>{{ $m($b->balance) }}</strong></td></tr>
                        @endif
                    </table>
                </td>
            </tr>
        </table>

        @php $payments = $b->payments->sortBy('created_at'); @endphp
        @if ($payments->isNotEmpty())
            <div class="label" style="margin-top: 26px;">{{ __('booking.payments') }}</div>
            <table class="items">
                <thead>
                    <tr>
                        <th>{{ __('booking.invoice.date') }}</th>
                        <th>{{ __('booking.invoice.method') }}</th>
                        <th>{{ __('booking.invoice.transaction') }}</th>
                        <th>{{ __('booking.invoice.status') }}</th>
                        <th class="num">{{ __('booking.invoice.amount') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($payments as $p)
                        <tr>
                            <td>{{ $p->created_at->translatedFormat('j M Y, H:i') }}</td>
                            <td>{{ $p->card_brand ?? __('booking.payment_methods.'.$p->method) }}@if ($p->card_last4) •••• {{ $p->card_last4 }}@endif</td>
                            <td class="muted small">{{ $p->transaction_id ?? '—' }}</td>
                            <td>{{ __('booking.payment_result.'.$p->status) }}</td>
                            <td class="num">{{ $m($p->amount) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif

        <div style="margin-top: 26px;" class="box small">
            <div class="label">{{ __('booking.invoice.notes') }}</div>
            {{ __('booking.invoice.payment_method_line', ['method' => __('booking.payment_methods.'.$b->payment_method)]) }}
            {{ __('booking.cancellation_policy', ['hours' => setting('free_cancellation_hours')]) }}
        </div>
    </div>

    <div class="footer">
        <table>
            <tr>
                <td>{{ __('booking.invoice.thanks') }} — {{ setting('hotel_name') }}</td>
                <td style="text-align: right;">{{ __('booking.invoice.demo_note') }}</td>
            </tr>
        </table>
    </div>
</body>
</html>
