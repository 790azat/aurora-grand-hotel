@php
    $b = $booking;
    $d = fn ($date, $f = 'D, j M Y') => \Illuminate\Support\Carbon::parse($date)->translatedFormat($f);
    $paid = $b->balance <= 0;
@endphp
<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="color-scheme" content="light">
    <title>{{ __('booking.mail.subject', ['reference' => $b->reference]) }}</title>
</head>
<body style="margin:0; padding:0; background:#f3efe6; font-family: Helvetica, Arial, sans-serif; color:#17150f;">
    <div style="display:none; max-height:0; overflow:hidden;">{{ __('booking.mail.preheader', ['room' => $b->roomType->name, 'date' => $d($b->check_in, 'j M Y')]) }}</div>
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f3efe6; padding:32px 12px;">
        <tr>
            <td align="center">
                <table role="presentation" width="600" cellpadding="0" cellspacing="0" style="max-width:600px; width:100%; background:#ffffff; border-radius:16px; overflow:hidden; border:1px solid #e6e0d3;">
                    {{-- Header --}}
                    <tr>
                        <td style="background:#0b1118; padding:34px 36px 30px; text-align:center;">
                            <div style="font-family: Georgia, 'Times New Roman', serif; font-size:28px; letter-spacing:7px; color:#ffffff;">AURORA</div>
                            <div style="font-size:9px; letter-spacing:5px; color:#cba65a; font-weight:bold; margin-top:4px;">GRAND HOTEL &amp; SPA</div>
                            <div style="margin:26px auto 0; width:56px; height:56px; line-height:56px; border-radius:28px; background:#cba65a; color:#0b1118; font-size:28px; font-weight:bold;">&#10003;</div>
                            <h1 style="font-family: Georgia, 'Times New Roman', serif; font-weight:normal; font-size:30px; color:#ffffff; margin:18px 0 6px;">{{ __('booking.mail.heading', ['name' => $b->first_name]) }}</h1>
                            <p style="margin:0; color:#c9c4b8; font-size:14px; line-height:1.5;">{{ __('booking.mail.intro') }}</p>
                        </td>
                    </tr>
                    {{-- Reference --}}
                    <tr>
                        <td style="padding:28px 36px 8px; text-align:center;">
                            <div style="font-size:10px; letter-spacing:3px; color:#6b665c; text-transform:uppercase; font-weight:bold;">{{ __('booking.reference') }}</div>
                            <div style="font-family: 'Courier New', monospace; font-size:26px; letter-spacing:4px; color:#9a763a; font-weight:bold; margin-top:4px;">{{ $b->reference }}</div>
                        </td>
                    </tr>
                    {{-- Stay --}}
                    <tr>
                        <td style="padding:16px 36px;">
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border:1px solid #e6e0d3; border-radius:12px;">
                                <tr>
                                    <td colspan="2" style="padding:16px 18px; border-bottom:1px solid #e6e0d3;">
                                        <div style="font-family: Georgia, serif; font-size:21px;">{{ $b->roomType->name }}</div>
                                        <div style="font-size:13px; color:#6b665c; margin-top:2px;">{{ trans_choice('booking.nights_count', $b->nights) }} · {{ trans_choice('booking.adults_count', $b->adults) }}@if ($b->children), {{ trans_choice('booking.children_count', $b->children) }}@endif</div>
                                    </td>
                                </tr>
                                <tr>
                                    <td width="50%" style="padding:14px 18px; border-right:1px solid #e6e0d3;">
                                        <div style="font-size:10px; letter-spacing:2px; color:#9a763a; text-transform:uppercase; font-weight:bold;">{{ __('booking.check_in') }}</div>
                                        <div style="font-size:15px; font-weight:bold; margin-top:3px;">{{ $d($b->check_in) }}</div>
                                        <div style="font-size:12px; color:#6b665c;">{{ __('booking.from_time', ['time' => setting('check_in_time')]) }}</div>
                                    </td>
                                    <td width="50%" style="padding:14px 18px;">
                                        <div style="font-size:10px; letter-spacing:2px; color:#9a763a; text-transform:uppercase; font-weight:bold;">{{ __('booking.check_out') }}</div>
                                        <div style="font-size:15px; font-weight:bold; margin-top:3px;">{{ $d($b->check_out) }}</div>
                                        <div style="font-size:12px; color:#6b665c;">{{ __('booking.until_time', ['time' => setting('check_out_time')]) }}</div>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    {{-- Price --}}
                    <tr>
                        <td style="padding:8px 36px 8px;">
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="font-size:14px;">
                                <tr><td style="padding:5px 0;">{{ __('booking.summary.room_nights', ['nights' => trans_choice('booking.nights_count', $b->nights)]) }}</td><td align="right">{{ money($b->room_total, true) }}</td></tr>
                                @foreach ($b->extras as $extra)
                                    <tr><td style="padding:5px 0; color:#6b665c;">{{ $extra->name }}@if ($extra->pivot->quantity > 1) × {{ $extra->pivot->quantity }}@endif</td><td align="right" style="color:#6b665c;">{{ money($extra->pivot->total, true) }}</td></tr>
                                @endforeach
                                @if ((float) $b->discount > 0)
                                    <tr><td style="padding:5px 0; color:#067647;">{{ __('booking.discount') }}@if ($b->promoCode) ({{ $b->promoCode->code }})@endif</td><td align="right" style="color:#067647;">−{{ money($b->discount, true) }}</td></tr>
                                @endif
                                <tr><td style="padding:5px 0; color:#6b665c;">{{ __('booking.tax') }}</td><td align="right" style="color:#6b665c;">{{ money($b->tax, true) }}</td></tr>
                                <tr><td style="padding:12px 0 5px; border-top:2px solid #17150f; font-weight:bold; font-size:16px;">{{ __('booking.summary.total') }}</td><td align="right" style="padding-top:12px; border-top:2px solid #17150f; font-weight:bold; font-size:20px; font-family: Georgia, serif;">{{ money($b->total, true) }}</td></tr>
                                <tr><td colspan="2" style="padding-top:6px;">
                                    <span style="display:inline-block; padding:4px 10px; border-radius:20px; font-size:12px; font-weight:bold; {{ $paid ? 'background:#dcfae6; color:#067647;' : 'background:#f3e9d2; color:#5c4625;' }}">
                                        {{ $paid ? __('booking.mail.paid_in_full') : __('booking.mail.pay_at_hotel', ['amount' => money($b->balance, true)]) }}
                                    </span>
                                </td></tr>
                            </table>
                        </td>
                    </tr>
                    {{-- CTA --}}
                    <tr>
                        <td style="padding:24px 36px 8px; text-align:center;">
                            <a href="{{ $url }}" style="display:inline-block; background:#b8914a; color:#ffffff; text-decoration:none; font-weight:bold; font-size:14px; padding:14px 30px; border-radius:30px;">{{ __('booking.mail.view_booking') }}</a>
                            <p style="font-size:12px; color:#6b665c; margin:14px 0 0;">{{ __('booking.mail.invoice_attached') }} <a href="{{ $invoiceUrl }}" style="color:#9a763a;">{{ __('booking.download_invoice') }}</a></p>
                        </td>
                    </tr>
                    {{-- Info --}}
                    <tr>
                        <td style="padding:20px 36px 28px;">
                            <div style="background:#faf8f3; border-radius:12px; padding:16px 18px; font-size:13px; line-height:1.6; color:#3d3a33;">
                                <strong>{{ __('booking.cancel.policy_title') }}</strong><br>
                                {{ __('booking.cancellation_policy', ['hours' => setting('free_cancellation_hours')]) }}
                            </div>
                        </td>
                    </tr>
                    {{-- Footer --}}
                    <tr>
                        <td style="background:#0b1118; padding:22px 36px; text-align:center; font-size:12px; line-height:1.7; color:#9a978f;">
                            {{ setting('hotel_name') }} · {{ \App\Models\Setting::localized('hotel_address') }}<br>
                            <a href="tel:{{ preg_replace('/[^\d+]/', '', setting('hotel_phone')) }}" style="color:#cba65a; text-decoration:none;">{{ setting('hotel_phone') }}</a> ·
                            <a href="mailto:{{ setting('hotel_email') }}" style="color:#cba65a; text-decoration:none;">{{ setting('hotel_email') }}</a><br>
                            <span style="font-size:11px; color:#6b665c;">{{ __('booking.mail.demo_note') }}</span>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
