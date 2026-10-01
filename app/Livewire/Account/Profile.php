<?php

namespace App\Livewire\Account;

use App\Http\Middleware\SetLocale;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Livewire\Component;

class Profile extends Component
{
    public string $name = '';

    public string $phone = '';

    public string $country = '';

    public string $locale = 'en';

    public string $current_password = '';

    public string $new_password = '';

    public string $new_password_confirmation = '';

    public string $delete_password = '';

    public function mount(): void
    {
        $user = Auth::user();
        $this->name = (string) $user->name;
        $this->phone = (string) $user->phone;
        $this->country = (string) $user->country;
        $this->locale = array_key_exists((string) $user->locale, SetLocale::LOCALES) ? $user->locale : app()->getLocale();
    }

    /** Demo accounts stay usable for every visitor: their password and existence are protected. */
    protected function isDemoAccount(): bool
    {
        return str_ends_with((string) Auth::user()->email, '@demo.com');
    }

    public function saveProfile()
    {
        $data = $this->validate([
            'name' => ['required', 'string', 'max:120'],
            'phone' => ['nullable', 'string', 'max:30', 'regex:/^[\d\s()+\-.]{6,}$/'],
            'country' => ['nullable', Rule::in(array_keys(__('booking.countries')))],
            'locale' => ['required', Rule::in(array_keys(SetLocale::LOCALES))],
        ]);

        $user = Auth::user();
        $user->update([
            'name' => trim($data['name']),
            'phone' => $data['phone'] ?: null,
            'country' => $data['country'] ?: null,
            'locale' => $data['locale'],
        ]);

        if ($data['locale'] !== app()->getLocale()) {
            session(['locale' => $data['locale']]);
            app()->setLocale($data['locale']);
            session()->flash('status', __('account.profile.saved'));

            return $this->redirectRoute('account.profile');
        }

        $this->dispatch('notify', message: __('account.profile.saved'), type: 'success');

        return null;
    }

    public function updatePassword(): void
    {
        if ($this->isDemoAccount()) {
            $this->addError('current_password', __('account.profile.demo_protected'));

            return;
        }

        $this->validate([
            'current_password' => ['required', 'current_password'],
            'new_password' => ['required', 'confirmed', 'different:current_password', Password::min(8)],
        ]);

        Auth::user()->update(['password' => Hash::make($this->new_password)]);
        $this->reset('current_password', 'new_password', 'new_password_confirmation');
        $this->dispatch('notify', message: __('account.profile.password_updated'), type: 'success');
    }

    public function deleteAccount()
    {
        if ($this->isDemoAccount()) {
            $this->addError('delete_password', __('account.profile.demo_protected'));

            return null;
        }

        $this->validate(['delete_password' => ['required', 'current_password']]);

        $user = Auth::user();
        Auth::logout();
        $user->delete();
        session()->invalidate();
        session()->regenerateToken();
        session()->flash('status', __('account.profile.deleted'));

        return $this->redirectRoute('home');
    }

    public function render()
    {
        return view('livewire.account.profile', [
            'countries' => __('booking.countries'),
        ])->title(__('account.profile.title'));
    }
}
