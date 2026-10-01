<?php

namespace App\Filament\Resources\Posts\Schemas;

use App\Filament\Support\Ui;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Html;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class PostForm
{
    public const CATEGORIES = ['news', 'travel', 'dining', 'wellness', 'events'];

    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(['default' => 1, 'lg' => 3])
            ->components([
                Group::make([
                    Section::make()->schema([
                        Ui::localeTabs(fn (string $l) => [
                            TextInput::make("title_{$l}")->label(Ui::l('title', $l))->required()->maxLength(255)
                                ->live(onBlur: true)
                                ->afterStateUpdated(function (Get $get, Set $set, ?string $state, string $operation) use ($l) {
                                    if ($l === 'en' && $operation === 'create' && blank($get('slug'))) {
                                        $set('slug', Str::slug((string) $state));
                                    }
                                }),
                            Textarea::make("excerpt_{$l}")->label(Ui::l('excerpt', $l))->rows(2),
                            RichEditor::make("body_{$l}")->label(Ui::l('body', $l))
                                ->toolbarButtons([['bold', 'italic', 'link'], ['h2', 'h3'], ['bulletList', 'orderedList', 'blockquote'], ['undo', 'redo']]),
                        ]),
                    ]),
                ])->columnSpan(['lg' => 2]),
                Group::make([
                    Section::make(__('admin.sections.publishing'))->schema([
                        TextInput::make('slug')->label(__('admin.fields.slug'))->required()->unique(ignoreRecord: true)->alphaDash(),
                        Select::make('category')->label(__('admin.fields.category'))
                            ->options(Ui::options('post_category', self::CATEGORIES))->default('news')->required()->native(false),
                        Toggle::make('is_published')->label(__('admin.fields.is_published'))->default(true),
                        DateTimePicker::make('published_at')->label(__('admin.fields.published_at'))->native(false)->default(now()),
                    ]),
                    Section::make(__('admin.fields.image'))->schema([
                        TextInput::make('image')->hiddenLabel()->url()->placeholder('https://images.unsplash.com/...')->live(onBlur: true),
                        Html::make(fn (Get $get) => view('filament.partials.image-preview', ['url' => $get('image')])),
                    ]),
                ])->columnSpan(['lg' => 1]),
            ]);
    }
}
