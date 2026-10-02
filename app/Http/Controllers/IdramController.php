<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Services\BookingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

/**
 * Idram merchant callbacks (used only when IDRAM_ACCOUNT / IDRAM_SECRET_KEY are set).
 *
 * In the Idram merchant cabinet set:
 *  RESULT_URL  → /payments/idram/result
 *  SUCCESS_URL → /payments/idram/success
 *  FAIL_URL    → /payments/idram/fail
 */
class IdramController
{
    /** Idram calls this twice: a pre-check (EDP_PRECHECK=YES), then the payment confirmation. */
    public function result(Request $request, BookingService $service): Response
    {
        $account = (string) config('services.idram.account');
        $booking = Booking::where('reference', $request->input('EDP_BILL_NO'))->first();

        if (! $booking || $request->input('EDP_REC_ACCOUNT') !== $account) {
            return response('Unknown bill or account', 400);
        }

        $transaction = (string) $request->input('EDP_TRANS_ID');
        $alreadyPaid = $transaction !== '' && $booking->payments()->where('transaction_id', $transaction)->exists();

        if (! $alreadyPaid && (int) round((float) $request->input('EDP_AMOUNT')) !== $service->idramAmount($booking)) {
            return response('Amount mismatch', 400);
        }

        if ($request->input('EDP_PRECHECK') === 'YES') {
            return response('OK');
        }

        $expected = md5(implode(':', [
            $account,
            $request->input('EDP_AMOUNT'),
            config('services.idram.secret'),
            $request->input('EDP_BILL_NO'),
            $request->input('EDP_PAYER_ACCOUNT'),
            $request->input('EDP_TRANS_ID'),
            $request->input('EDP_TRANS_DATE'),
        ]));

        if (! hash_equals(strtoupper($expected), strtoupper((string) $request->input('EDP_CHECKSUM')))) {
            Log::warning('Idram checksum mismatch', ['bill' => $booking->reference]);

            return response('Checksum mismatch', 400);
        }

        if (! $alreadyPaid && $booking->balance > 0) {
            $service->payIdram($booking, (string) $request->input('EDP_PAYER_ACCOUNT'), $transaction);
        }

        return response('OK');
    }

    public function success(Request $request): RedirectResponse
    {
        $booking = Booking::where('reference', $request->input('EDP_BILL_NO'))->firstOrFail();
        session()->flash('payment_success', true);

        return redirect()->route('booking.confirmation', $booking);
    }

    public function fail(Request $request): RedirectResponse
    {
        $booking = Booking::where('reference', $request->input('EDP_BILL_NO'))->firstOrFail();
        session()->flash('status', __('booking.idram.failed'));

        return redirect()->route('booking.pay', ['booking' => $booking, 'method' => 'idram']);
    }
}
