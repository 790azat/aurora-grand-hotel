<?php

namespace App\Livewire\Auth;

use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Livewire\Component;

/**
 * Demo password reset: no real emails are sent. The request is written to the
 * application log so the flow can be shown without exposing any account.
 */
class ForgotPassword extends Component
{
    public string $email = '';

    public bool $sent = false;

    public function send(): void
    {
        $this->email = Str::lower(trim($this->email));
        $this->validate(['email' => ['required', 'email']]);

        $key = 'forgot-password:'.request()->ip();
        if (RateLimiter::tooManyAttempts($key, 5)) {
            $this->addError('email', __('passwords.throttled'));

            return;
        }
        RateLimiter::hit($key, 60);

        if (User::where('email', $this->email)->exists()) {
            Log::info('[demo] Password reset requested', ['email' => $this->email]);
        }

        // Same answer whether or not the account exists (no account enumeration).
        $this->sent = true;
    }

    public function render()
    {
        return view('livewire.auth.forgot-password')->title(__('account.auth.forgot_title'));
    }
}
