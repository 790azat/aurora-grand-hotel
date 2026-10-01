<?php

namespace App\Http\Controllers;

use App\Http\Middleware\SetLocale;
use App\Models\Booking;
use App\Services\BookingService;
use Illuminate\Http\Request;

class InvoiceController
{
    /** Download the PDF invoice of a booking (guest session, owner, staff or signed link). */
    public function __invoke(Request $request, Booking $booking, BookingService $service)
    {
        if (! $service->canAccess($booking)) {
            if ($request->user()) {
                abort(403);
            }

            return redirect()->route('booking.lookup', ['ref' => $booking->reference])
                ->with('status', __('booking.errors.access_denied'));
        }

        // ?lang=ru|en overrides; otherwise the visitor's current site language.
        $locale = $request->query('lang');
        $locale = array_key_exists((string) $locale, SetLocale::LOCALES) ? $locale : app()->getLocale();

        $pdf = $service->invoicePdf($booking, $locale);
        $filename = $service->invoiceFilename($booking);

        return response($pdf, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => ($request->boolean('inline') ? 'inline' : 'attachment').'; filename="'.$filename.'"',
            'Cache-Control' => 'private, no-store',
        ]);
    }
}
