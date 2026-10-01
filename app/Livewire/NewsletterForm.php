<?php

namespace App\Livewire;

use App\Models\Subscriber;
use Livewire\Component;

class NewsletterForm extends Component
{
    public string $email = '';

    public bool $subscribed = false;

    protected function rules(): array
    {
        return ['email' => 'required|email:rfc|max:150|unique:subscribers,email'];
    }

    protected function messages(): array
    {
        return [
            'email.unique' => __('site.newsletter.already'),
            'email.required' => __('site.newsletter.required'),
            'email.email' => __('site.newsletter.invalid'),
        ];
    }

    public function subscribe(): void
    {
        $this->email = mb_strtolower(trim($this->email));
        $this->validate();

        Subscriber::create(['email' => $this->email, 'locale' => app()->getLocale()]);

        $this->subscribed = true;
        $this->dispatch('notify', message: __('site.newsletter.success'), type: 'success');
    }

    public function render()
    {
        return view('livewire.newsletter-form');
    }
}
