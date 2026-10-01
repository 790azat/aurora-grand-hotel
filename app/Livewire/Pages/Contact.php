<?php

namespace App\Livewire\Pages;

use App\Models\ContactMessage;
use Livewire\Attributes\Validate;
use Livewire\Component;

class Contact extends Component
{
    #[Validate('required|string|min:2|max:100')]
    public string $name = '';

    #[Validate('required|email:rfc|max:150')]
    public string $email = '';

    #[Validate('nullable|string|max:40|regex:/^[0-9+\-\s()]{6,}$/')]
    public string $phone = '';

    #[Validate('required|string|in:reservation,event,spa,feedback,other')]
    public string $subject = 'reservation';

    #[Validate('required|string|min:10|max:3000')]
    public string $message = '';

    public bool $sent = false;

    public const SUBJECTS = ['reservation', 'event', 'spa', 'feedback', 'other'];

    public function mount(): void
    {
        if ($user = auth()->user()) {
            $this->name = (string) $user->name;
            $this->email = (string) $user->email;
            $this->phone = (string) $user->phone;
        }
    }

    protected function validationAttributes(): array
    {
        return [
            'name' => mb_strtolower(__('site.contact.name')),
            'email' => 'email',
            'phone' => mb_strtolower(__('site.contact.phone')),
            'subject' => mb_strtolower(__('site.contact.subject')),
            'message' => mb_strtolower(__('site.contact.message')),
        ];
    }

    public function send(): void
    {
        $data = $this->validate();

        ContactMessage::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?: null,
            'subject' => __('site.contact.subjects.'.$data['subject'], [], 'en'),
            'message' => $data['message'],
        ]);

        $this->reset('message', 'phone');
        $this->subject = 'reservation';
        $this->sent = true;
        $this->dispatch('notify', message: __('site.contact.success'), type: 'success');
    }

    public function render()
    {
        return view('livewire.pages.contact')
            ->title(__('site.contact.meta_title'))
            ->layoutData(['description' => __('site.contact.meta_description')]);
    }
}
