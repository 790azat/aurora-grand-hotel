<?php

namespace App\Filament\Resources\Reviews\Schemas;

use App\Models\RoomType;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\ToggleButtons;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class ReviewForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(['default' => 1, 'lg' => 3])
            ->components([
                Group::make([
                    Section::make(__('admin.review.review'))->icon(Heroicon::OutlinedStar)->columns(['default' => 1, 'sm' => 2])->schema([
                        ToggleButtons::make('rating')->label(__('admin.fields.rating'))
                            ->options([1 => '★', 2 => '★★', 3 => '★★★', 4 => '★★★★', 5 => '★★★★★'])
                            ->default(5)->inline()->required()->columnSpanFull(),
                        TextInput::make('title')->label(__('admin.fields.title'))->maxLength(255)->columnSpanFull(),
                        Textarea::make('body')->label(__('admin.fields.review_text'))->required()->rows(5)->columnSpanFull(),
                    ]),
                    Section::make(__('admin.review.reply'))->icon(Heroicon::OutlinedChatBubbleLeftRight)
                        ->description(__('admin.review.reply_hint'))
                        ->schema([
                            Textarea::make('reply')->hiddenLabel()->rows(4),
                        ]),
                ])->columnSpan(['lg' => 2]),
                Group::make([
                    Section::make(__('admin.fields.guest'))->schema([
                        TextInput::make('name')->label(__('admin.fields.name'))->required(),
                        TextInput::make('country')->label(__('admin.fields.country')),
                        Select::make('room_type_id')->label(__('admin.fields.room_type'))
                            ->options(fn () => RoomType::orderBy('sort')->get()->pluck('name', 'id'))->native(false),
                        Select::make('locale')->label(__('admin.fields.language'))->options(['en' => 'English', 'ru' => 'Русский'])->default('en')->native(false)->required(),
                        Toggle::make('is_approved')->label(__('admin.fields.is_approved')),
                    ]),
                ])->columnSpan(['lg' => 1]),
            ]);
    }
}
