<?php

namespace App\Filament\Resources\Facilities\Schemas;

use App\Filament\Support\Ui;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Html;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class FacilityForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(['default' => 1, 'lg' => 3])
            ->components([
                Section::make()->schema([
                    Ui::localeTabs(fn (string $l) => [
                        TextInput::make("name_{$l}")->label(Ui::l('name', $l))->required()->maxLength(255),
                        Textarea::make("description_{$l}")->label(Ui::l('description', $l))->rows(6),
                    ]),
                ])->columnSpan(['lg' => 2]),
                Group::make([
                    Section::make()->schema([
                        TextInput::make('slug')->label(__('admin.fields.slug'))->required()->unique(ignoreRecord: true)->alphaDash(),
                        TextInput::make('hours')->label(__('admin.fields.hours'))->placeholder('07:00 – 22:00'),
                        TextInput::make('icon')->label(__('admin.fields.icon'))->default('sparkles')->required(),
                        TextInput::make('sort')->label(__('admin.fields.sort'))->numeric()->default(0),
                        Toggle::make('is_active')->label(__('admin.fields.is_active'))->default(true),
                    ]),
                    Section::make(__('admin.fields.image'))->schema([
                        TextInput::make('image')->hiddenLabel()->url()->live(onBlur: true),
                        Html::make(fn (Get $get) => view('filament.partials.image-preview', ['url' => $get('image')])),
                    ]),
                ])->columnSpan(['lg' => 1]),
            ]);
    }
}
