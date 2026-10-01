<?php

namespace App\Filament\Resources\PromoCodes\Schemas;

use App\Filament\Support\Ui;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\ToggleButtons;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class PromoCodeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make()->columns(['default' => 1, 'sm' => 2])->columnSpanFull()->schema([
                TextInput::make('code')->label(__('admin.fields.code'))->required()->unique(ignoreRecord: true)->maxLength(40)
                    ->extraInputAttributes(['style' => 'text-transform:uppercase;font-family:monospace'])
                    ->dehydrateStateUsing(fn ($state) => strtoupper(trim((string) $state))),
                ToggleButtons::make('type')->label(__('admin.fields.discount_type'))
                    ->options(Ui::options('promo_type', ['percent', 'fixed']))
                    ->default('percent')->inline()->required()->live(),
                TextInput::make('value')->label(__('admin.fields.value'))->numeric()->required()->minValue(0)
                    ->suffix(fn (Get $get) => $get('type') === 'percent' ? '%' : setting('currency_symbol', '$'))
                    ->maxValue(fn (Get $get) => $get('type') === 'percent' ? 100 : null),
                TextInput::make('min_nights')->label(__('admin.fields.min_nights'))->numeric()->minValue(1),
                DatePicker::make('valid_from')->label(__('admin.fields.valid_from'))->native(false),
                DatePicker::make('valid_until')->label(__('admin.fields.valid_until'))->native(false)->afterOrEqual('valid_from'),
                TextInput::make('max_uses')->label(__('admin.fields.max_uses'))->numeric()->minValue(1)->helperText(__('admin.promo.unlimited_hint')),
                TextInput::make('used_count')->label(__('admin.fields.used_count'))->numeric()->default(0)->disabled()->dehydrated(false),
                Toggle::make('is_active')->label(__('admin.fields.is_active'))->default(true),
            ]),
        ]);
    }
}
