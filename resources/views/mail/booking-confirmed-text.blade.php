{!! __('booking.mail.heading', ['name' => $booking->first_name]) !!}

{!! __('booking.mail.intro') !!}

{!! __('booking.reference') !!}: {!! $booking->reference !!}
{!! $booking->roomType->name !!} — {!! trans_choice('booking.nights_count', $booking->nights) !!}
{!! __('booking.check_in') !!}: {!! $booking->check_in->translatedFormat('D, j M Y') !!} ({!! __('booking.from_time', ['time' => setting('check_in_time')]) !!})
{!! __('booking.check_out') !!}: {!! $booking->check_out->translatedFormat('D, j M Y') !!} ({!! __('booking.until_time', ['time' => setting('check_out_time')]) !!})
{!! __('booking.summary.total') !!}: {!! money($booking->total, true) !!}

{!! __('booking.mail.view_booking') !!}: {!! $url !!}

{!! setting('hotel_name') !!} · {!! setting('hotel_phone') !!} · {!! setting('hotel_email') !!}
