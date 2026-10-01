<?php

namespace App\Livewire\Auth;

use App\Http\Middleware\SetLocale;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Component;

class Login extends Component
{
    public string $email = '';

    public string $password = '';

    public bool $remember = false;

    public function mount(): void
    {
        // ?redirect=/booking?… lets the booking wizard send guests back after signing in.
        $redirect = (string) request()->query('redirect', '');
        if ($redirect !== '' && str_starts_with($redirect, '/') && ! str_starts_with($redirect, '//')) {
            session()->put('url.intended', url($redirect));
        }
    }

    public function fillDemo(): void
    {
        $this->email = 'guest@demo.com';
        $this->password = 'password';
        $this->resetErrorBag();
    }

    public function login()
    {
        $this->email = Str::lower(trim($this->email));
        $this->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $key = Str::transliterate($this->email.'|'.request()->ip());
        if (RateLimiter::tooManyAttempts($key, 5)) {
            event(new Lockout(request()));
            throw ValidationException::withMessages([
                'email' => __('auth.throttle', ['seconds' => RateLimiter::availableIn($key)]),
            ]);
        }

        if (! Auth::attempt(['email' => $this->email, 'password' => $this->password], $this->remember)) {
            RateLimiter::hit($key, 60);
            $this->password = '';
            throw ValidationException::withMessages(['email' => __('auth.failed')]);
        }

        RateLimiter::clear($key);
        session()->regenerate();

        $user = Auth::user();
        if ($user->locale && $user->locale !== app()->getLocale() && array_key_exists($user->locale, SetLocale::LOCALES)) {
            session(['locale' => $user->locale]);
        }

        if ($user->isStaff()) {
            session()->forget('url.intended');

            return $this->redirect(url('/admin'));
        }

        return $this->redirectIntended(route('account.dashboard'));
    }

    public function render()
    {
        return view('livewire.auth.login')->title(__('account.auth.login_title'));
    }
}
