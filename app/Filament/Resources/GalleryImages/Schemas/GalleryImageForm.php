<?php

namespace App\Filament\Resources\GalleryImages\Schemas;

use App\Filament\Support\Ui;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Html;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class GalleryImageForm
{
    public const CATEGORIES = ['hotel', 'rooms', 'spa', 'dining', 'beach'];

    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make()->columns(['default' => 1, 'sm' => 2])->columnSpanFull()->schema([
                TextInput::make('url')->label(__('admin.fields.image_url'))->url()->required()->live(onBlur: true)->columnSpanFull(),
                Html::make(fn (Get $get) => view('filament.partials.image-preview', ['url' => $get('url')]))->columnSpanFull(),
                TextInput::make('caption_en')->label(Ui::l('caption', 'en')),
                TextInput::make('caption_ru')->label(Ui::l('caption', 'ru')),
                TextInput::make('caption_hy')->label(Ui::l('caption', 'hy')),
                Select::make('category')->label(__('admin.fields.category'))->options(Ui::options('gallery_category', self::CATEGORIES))->default('hotel')->required()->native(false),
                TextInput::make('sort')->label(__('admin.fields.sort'))->numeric()->default(0),
            ]),
        ]);
    }
}
