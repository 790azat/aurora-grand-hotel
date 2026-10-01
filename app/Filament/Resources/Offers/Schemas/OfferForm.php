<?php

namespace App\Filament\Resources\Offers\Schemas;

use App\Filament\Support\Ui;
use App\Models\PromoCode;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Html;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class OfferForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(['default' => 1, 'lg' => 3])
            ->components([
                Section::make()->schema([
                    Ui::localeTabs(fn (string $l) => [
                        TextInput::make("title_{$l}")->label(Ui::l('title', $l))->required()->maxLength(255),
                        TextInput::make("badge_{$l}")->label(Ui::l('badge', $l))->maxLength(40),
                        Textarea::make("description_{$l}")->label(Ui::l('description', $l))->rows(5),
                    ]),
                ])->columnSpan(['lg' => 2]),
                Group::make([
                    Section::make()->schema([
                        Select::make('promo_code')->label(__('admin.fields.promo_code'))
                            ->options(fn () => PromoCode::orderBy('code')->pluck('code', 'code'))->searchable()->native(false),
                        DatePicker::make('valid_until')->label(__('admin.fields.valid_until'))->native(false),
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
