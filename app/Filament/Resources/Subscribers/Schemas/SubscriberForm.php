<?php

namespace App\Filament\Resources\Subscribers\Schemas;

use App\Http\Middleware\SetLocale;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class SubscriberForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('email')->label(__('admin.fields.email'))->email()->required()->unique(ignoreRecord: true),
            Select::make('locale')->label(__('admin.fields.language'))->options(SetLocale::LOCALES)->default('en')->required()->native(false),
        ])->columns(1);
    }
}
