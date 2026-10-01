<?php

namespace App\Mail;

use App\Models\Booking;
use App\Services\BookingService;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\URL;

/** Branded booking confirmation with the PDF invoice attached, localized by the booking locale. */
class BookingConfirmed extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Booking $booking)
    {
        $this->locale($booking->locale ?: app()->getLocale());
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            from: new Address((string) setting('hotel_email'), (string) setting('hotel_name')),
            subject: __('booking.mail.subject', ['reference' => $this->booking->reference]),
            tags: ['booking-confirmation'],
        );
    }

    public function content(): Content
    {
        $this->booking->loadMissing(['roomType', 'extras', 'promoCode']);

        return new Content(
            view: 'mail.booking-confirmed',
            text: 'mail.booking-confirmed-text',
            with: [
                'booking' => $this->booking,
                'url' => URL::signedRoute('booking.confirmation', $this->booking),
                'invoiceUrl' => URL::signedRoute('booking.invoice', $this->booking),
            ],
        );
    }

    /** @return array<int, Attachment> */
    public function attachments(): array
    {
        $service = app(BookingService::class);
        $booking = $this->booking;
        $locale = $this->locale;

        return [
            Attachment::fromData(fn () => $service->invoicePdf($booking, $locale), $service->invoiceFilename($booking))
                ->withMime('application/pdf'),
        ];
    }
}
