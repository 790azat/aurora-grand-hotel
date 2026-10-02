<?php

namespace App\Filament\Resources\Amenities\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class AmenityForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make()->columns(['default' => 1, 'sm' => 2])->schema([
                TextInput::make('name_en')->label(__('admin.fields.name').' (EN)')->required()->maxLength(255),
                TextInput::make('name_ru')->label(__('admin.fields.name').' (RU)')->required()->maxLength(255),
                TextInput::make('name_hy')->label(__('admin.fields.name').' (HY)')->maxLength(255),
                TextInput::make('icon')->label(__('admin.fields.icon'))->required()->default('sparkles')
                    ->helperText(__('admin.misc.icon_hint')),
                TextInput::make('sort')->label(__('admin.fields.sort'))->numeric()->default(0)->required(),
            ])->columnSpanFull(),
        ]);
    }
}
