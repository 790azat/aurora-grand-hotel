<?php

namespace App\Filament\Resources\RoomTypes\Schemas;

use App\Filament\Support\Ui;
use App\Models\Amenity;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Html;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Str;

class RoomTypeForm
{
    public static function configure(Schema $schema): Schema
    {
        $symbol = setting('currency_symbol', '$');

        return $schema
            ->columns(['default' => 1, 'lg' => 3])
            ->components([
                Group::make([
                    Section::make(__('admin.sections.content'))
                        ->icon(Heroicon::OutlinedLanguage)
                        ->schema([
                            Ui::localeTabs(fn (string $l) => [
                                TextInput::make("name_{$l}")->label(Ui::l('name', $l))->required()->maxLength(255)
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(function (Get $get, Set $set, ?string $state, string $operation) use ($l) {
                                        if ($l === 'en' && $operation === 'create' && blank($get('slug'))) {
                                            $set('slug', Str::slug((string) $state));
                                        }
                                    }),
                                TextInput::make("short_{$l}")->label(Ui::l('short', $l))->maxLength(255),
                                Textarea::make("description_{$l}")->label(Ui::l('description', $l))->rows(5),
                                Grid::make(2)->schema([
                                    TextInput::make("beds_{$l}")->label(Ui::l('beds', $l)),
                                    TextInput::make("view_{$l}")->label(Ui::l('view', $l)),
                                ]),
                            ]),
                        ]),
                    Section::make(__('admin.sections.images'))
                        ->icon(Heroicon::OutlinedPhoto)
                        ->description(__('admin.room_type.images_hint'))
                        ->schema([
                            Repeater::make('images')
                                ->hiddenLabel()
                                ->simple(
                                    TextInput::make('url')->url()->required()->placeholder('https://images.unsplash.com/...'),
                                )
                                ->reorderable()
                                ->addActionLabel(__('admin.room_type.add_image'))
                                ->defaultItems(1)
                                ->live(onBlur: true),
                            Html::make(fn (Get $get) => view('filament.partials.image-strip', ['urls' => collect((array) $get('images'))->map(fn ($v) => is_array($v) ? ($v['url'] ?? reset($v)) : $v)->filter()->values()->all()])),
                        ]),
                ])->columnSpan(['lg' => 2]),
                Group::make([
                    Section::make(__('admin.sections.pricing'))
                        ->icon(Heroicon::OutlinedCurrencyDollar)
                        ->schema([
                            TextInput::make('slug')->label(__('admin.fields.slug'))->required()->unique(ignoreRecord: true)->alphaDash(),
                            TextInput::make('base_price')->label(__('admin.fields.base_price'))->numeric()->prefix($symbol)->required()->minValue(0),
                            TextInput::make('weekend_price')->label(__('admin.fields.weekend_price'))->numeric()->prefix($symbol)->minValue(0)
                                ->helperText(__('admin.room_type.weekend_hint')),
                            TextInput::make('min_nights')->label(__('admin.fields.min_nights'))->numeric()->minValue(1)->default(1)->required(),
                        ]),
                    Section::make(__('admin.sections.capacity'))
                        ->icon(Heroicon::OutlinedUsers)
                        ->columns(2)
                        ->schema([
                            TextInput::make('max_adults')->label(__('admin.fields.max_adults'))->numeric()->minValue(1)->default(2)->required(),
                            TextInput::make('max_children')->label(__('admin.fields.max_children'))->numeric()->minValue(0)->default(1)->required(),
                            TextInput::make('size_m2')->label(__('admin.fields.size_m2'))->numeric()->suffix('m²')->default(30)->required(),
                            TextInput::make('sort')->label(__('admin.fields.sort'))->numeric()->default(0),
                        ]),
                    Section::make(__('admin.models.amenity.plural'))
                        ->icon(Heroicon::OutlinedSparkles)
                        ->schema([
                            Select::make('amenities')
                                ->hiddenLabel()
                                ->relationship('amenities', 'name_en')
                                ->getOptionLabelFromRecordUsing(fn (Amenity $a) => $a->name)
                                ->multiple()
                                ->preload()
                                ->searchable(),
                        ]),
                    Section::make(__('admin.sections.visibility'))
                        ->schema([
                            Toggle::make('is_active')->label(__('admin.fields.is_active'))->default(true),
                            Toggle::make('is_featured')->label(__('admin.fields.is_featured')),
                        ]),
                ])->columnSpan(['lg' => 1]),
            ]);
    }
}
