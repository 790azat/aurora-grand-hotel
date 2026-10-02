<?php

namespace App\Filament\Resources\Posts\Tables;

use App\Filament\Resources\Posts\Schemas\PostForm;
use App\Filament\Support\Ui;
use App\Models\Post;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class PostsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('published_at', 'desc')
            ->columns([
                ImageColumn::make('image')->label('')->imageWidth(72)->imageHeight(48)
                    ->extraImgAttributes(['style' => 'border-radius:.5rem;object-fit:cover', 'loading' => 'lazy']),
                TextColumn::make('title')->label(__('admin.fields.title'))->state(fn (Post $p) => $p->title)
                    ->description(fn (Post $p) => '/blog/'.$p->slug)->weight('semibold')->wrap()
                    ->searchable(['title_en', 'title_ru', 'title_hy', 'slug']),
                TextColumn::make('category')->label(__('admin.fields.category'))->formatStateUsing(fn ($state) => __('admin.post_category.'.$state))->badge()->color('gray'),
                TextColumn::make('published_at')->label(__('admin.fields.published_at'))->date('d M Y')->sortable(),
                ToggleColumn::make('is_published')->label(__('admin.fields.is_published')),
            ])
            ->filters([
                SelectFilter::make('category')->label(__('admin.fields.category'))->options(Ui::options('post_category', PostForm::CATEGORIES)),
                TernaryFilter::make('is_published')->label(__('admin.fields.is_published')),
            ])
            ->recordActions([
                Action::make('open')->label(__('admin.actions.open_site'))->icon('heroicon-o-arrow-top-right-on-square')->color('gray')->iconButton()
                    ->url(fn (Post $p) => url('/blog/'.$p->slug))->openUrlInNewTab(),
                EditAction::make(),
            ])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }
}
