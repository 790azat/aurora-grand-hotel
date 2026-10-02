<?php

use App\Http\Controllers\IdramController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\LocaleController;
use App\Http\Controllers\SeoController;
use App\Http\Middleware\EnsureDemoDatabase;
use App\Livewire;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

// Language switch (stores locale in session + cookie, redirects back)
Route::get('/lang/{locale}', LocaleController::class)->name('locale.switch');

// Public website
Route::livewire('/', Livewire\Pages\Home::class)->name('home');
Route::livewire('/rooms', Livewire\Rooms\Index::class)->name('rooms.index');
Route::livewire('/rooms/{roomType:slug}', Livewire\Rooms\Show::class)->name('rooms.show');
Route::livewire('/facilities', Livewire\Pages\Facilities::class)->name('facilities');
Route::livewire('/offers', Livewire\Pages\Offers::class)->name('offers');
Route::livewire('/gallery', Livewire\Pages\Gallery::class)->name('gallery');
Route::livewire('/about', Livewire\Pages\About::class)->name('about');
Route::livewire('/contact', Livewire\Pages\Contact::class)->name('contact');
Route::livewire('/faq', Livewire\Pages\Faq::class)->name('faq');
Route::livewire('/reviews', Livewire\Pages\Reviews::class)->name('reviews');
Route::livewire('/blog', Livewire\Blog\Index::class)->name('blog.index');
Route::livewire('/blog/{post:slug}', Livewire\Blog\Show::class)->name('blog.show');
Route::view('/privacy', 'pages.privacy')->name('privacy');
Route::view('/terms', 'pages.terms')->name('terms');
Route::get('/sitemap.xml', [SeoController::class, 'sitemap'])->name('sitemap');
Route::get('/robots.txt', [SeoController::class, 'robots']);

// Booking engine
Route::livewire('/booking', Livewire\Booking\Wizard::class)->name('booking');
Route::livewire('/booking/{booking:reference}/pay', Livewire\Booking\Payment::class)->name('booking.pay');
Route::livewire('/booking/{booking:reference}/confirmation', Livewire\Booking\Confirmation::class)->name('booking.confirmation');
Route::get('/booking/{booking:reference}/invoice', InvoiceController::class)->name('booking.invoice');
Route::livewire('/manage-booking', Livewire\Booking\Lookup::class)->name('booking.lookup');

// Guest authentication
Route::middleware('guest')->group(function () {
    Route::livewire('/login', Livewire\Auth\Login::class)->name('login');
    Route::livewire('/register', Livewire\Auth\Register::class)->name('register');
    Route::livewire('/forgot-password', Livewire\Auth\ForgotPassword::class)->name('password.request');
});
Route::post('/logout', function () {
    Auth::logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();

    return redirect()->route('home');
})->middleware('auth')->name('logout');

// Guest account
Route::middleware('auth')->prefix('account')->name('account.')->group(function () {
    Route::livewire('/', Livewire\Account\Dashboard::class)->name('dashboard');
    Route::livewire('/bookings/{booking:reference}', Livewire\Account\BookingShow::class)->name('bookings.show');
    Route::livewire('/profile', Livewire\Account\Profile::class)->name('profile');
});

// Demo maintenance: Vercel Cron calls this daily to restore fresh demo data.
Route::get('/demo/reset', function () {
    abort_unless(request()->bearerToken() === env('CRON_SECRET') && env('CRON_SECRET'), 403);
    EnsureDemoDatabase::ensure(fresh: true);

    return response()->json(['reset' => true]);
})->name('demo.reset');

// Idram payment gateway callbacks (live mode only; see IdramController).
Route::post('/payments/idram/result', [IdramController::class, 'result'])->name('idram.result');
Route::match(['get', 'post'], '/payments/idram/success', [IdramController::class, 'success'])->name('idram.success');
Route::match(['get', 'post'], '/payments/idram/fail', [IdramController::class, 'fail'])->name('idram.fail');
