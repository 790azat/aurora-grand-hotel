<?php

namespace App\Livewire\Auth;

use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Livewire\Component;

class Register extends Component
{
    public string $name = '';

    public string $email = '';

    public string $phone = '';

    public string $password = '';

    public string $password_confirmation = '';

    public function register()
    {
        $this->email = Str::lower(trim($this->email));
        $data = $this->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:120', Rule::unique('users', 'email')],
            'phone' => ['nullable', 'string', 'max:30', 'regex:/^[\d\s()+\-.]{6,}$/'],
            'password' => ['required', 'confirmed', Password::min(8)],
        ]);

        $user = User::create([
            'name' => trim($data['name']),
            'email' => $data['email'],
            'phone' => $data['phone'] ?: null,
            'password' => $data['password'],
            'role' => 'guest',
            'locale' => app()->getLocale(),
        ]);

        event(new Registered($user));
        Auth::login($user);
        session()->regenerate();
        session()->flash('status', __('account.auth.welcome', ['name' => $user->name]));

        return $this->redirectIntended(route('account.dashboard'));
    }

    public function render()
    {
        return view('livewire.auth.register')->title(__('account.auth.register_title'));
    }
}
