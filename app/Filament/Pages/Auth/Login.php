<?php

namespace App\Filament\Pages\Auth;

use Filament\Auth\Pages\Login as BaseLogin;
use Filament\Schemas\Components\View;
use Filament\Schemas\Schema;

/** Login with a demo-credentials hint and pre-filled admin account. */
class Login extends BaseLogin
{
    public function mount(): void
    {
        parent::mount();

        $this->form->fill([
            'email' => 'admin@demo.com',
            'password' => 'password',
            'remember' => true,
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            View::make('filament.login-hint'),
            $this->getEmailFormComponent(),
            $this->getPasswordFormComponent(),
            $this->getRememberFormComponent(),
        ]);
    }
}
