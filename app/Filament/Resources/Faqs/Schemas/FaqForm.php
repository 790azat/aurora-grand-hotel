<?php

namespace App\Filament\Resources\Faqs\Schemas;

use App\Filament\Support\Ui;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class FaqForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make()->columnSpanFull()->schema([
                Ui::localeTabs(fn (string $l) => [
                    TextInput::make("question_{$l}")->label(Ui::l('question', $l))->required()->maxLength(255),
                    Textarea::make("answer_{$l}")->label(Ui::l('answer', $l))->required()->rows(5),
                ]),
            ]),
            Section::make()->columns(2)->columnSpanFull()->schema([
                TextInput::make('sort')->label(__('admin.fields.sort'))->numeric()->default(0),
                Toggle::make('is_active')->label(__('admin.fields.is_active'))->default(true)->inline(false),
            ]),
        ]);
    }
}
