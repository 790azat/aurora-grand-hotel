<?php

namespace App\Filament\Resources\Extras\Schemas;

use App\Filament\Support\Ui;
use App\Models\Extra;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ExtraForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make()->columnSpanFull()->schema([
                Ui::localeTabs(fn (string $l) => [
                    TextInput::make("name_{$l}")->label(Ui::l('name', $l))->required()->maxLength(255),
                    TextInput::make("description_{$l}")->label(Ui::l('description', $l))->maxLength(255),
                ]),
            ]),
            Section::make()->columns(['default' => 1, 'sm' => 2, 'lg' => 4])->columnSpanFull()->schema([
                TextInput::make('price')->label(__('admin.fields.price'))->numeric()->prefix(setting('currency_symbol', '$'))->required()->minValue(0),
                Select::make('pricing')->label(__('admin.fields.pricing'))->options(Ui::options('pricing', Extra::PRICING))->default('per_stay')->required()->native(false),
                TextInput::make('icon')->label(__('admin.fields.icon'))->default('sparkles')->required(),
                TextInput::make('sort')->label(__('admin.fields.sort'))->numeric()->default(0),
                Toggle::make('is_active')->label(__('admin.fields.is_active'))->default(true),
            ]),
        ]);
    }
}
