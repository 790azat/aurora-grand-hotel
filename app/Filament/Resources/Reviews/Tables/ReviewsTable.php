<?php

namespace App\Filament\Resources\Reviews\Tables;

use App\Models\Review;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;

class ReviewsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->modifyQueryUsing(fn ($query) => $query->with('roomType'))
            ->columns([
                TextColumn::make('rating')->label(__('admin.fields.rating'))
                    ->formatStateUsing(fn ($state) => str_repeat('★', (int) $state).str_repeat('☆', 5 - (int) $state))
                    ->color(fn ($state) => $state >= 4 ? 'primary' : ($state == 3 ? 'warning' : 'danger'))
                    ->size('lg')->sortable(),
                TextColumn::make('name')->label(__('admin.fields.guest'))->searchable()->weight('semibold')
                    ->description(fn (Review $r) => trim(($r->country ?? '').' · '.strtoupper($r->locale), ' ·')),
                TextColumn::make('body')->label(__('admin.fields.review_text'))
                    ->formatStateUsing(fn (Review $r) => Str::limit(($r->title ? $r->title.' — ' : '').$r->body, 120))
                    ->wrap()->searchable(['title', 'body']),
                TextColumn::make('roomType.name')->label(__('admin.fields.room_type'))->badge()->color('gray')->placeholder('—')->toggleable(),
                TextColumn::make('reply')->label(__('admin.review.reply'))
                    ->formatStateUsing(fn ($state) => $state ? __('admin.review.replied') : null)
                    ->placeholder('—')->badge()->color('info')->toggleable(),
                ToggleColumn::make('is_approved')->label(__('admin.fields.is_approved')),
                TextColumn::make('created_at')->label(__('admin.fields.date'))->date('d M Y')->sortable(),
            ])
            ->filters([
                TernaryFilter::make('is_approved')->label(__('admin.fields.is_approved')),
                SelectFilter::make('rating')->label(__('admin.fields.rating'))->options([5 => '★★★★★', 4 => '★★★★', 3 => '★★★', 2 => '★★', 1 => '★']),
            ])
            ->recordActions([
                Action::make('approve')->label(__('admin.actions.approve'))->icon(Heroicon::OutlinedCheck)->color('success')->iconButton()
                    ->tooltip(__('admin.actions.approve'))
                    ->visible(fn (Review $r) => ! $r->is_approved)
                    ->action(fn (Review $r) => $r->update(['is_approved' => true])),
                Action::make('reject')->label(__('admin.actions.reject'))->icon(Heroicon::OutlinedNoSymbol)->color('danger')->iconButton()
                    ->tooltip(__('admin.actions.reject'))
                    ->visible(fn (Review $r) => $r->is_approved)
                    ->action(fn (Review $r) => $r->update(['is_approved' => false])),
                Action::make('reply')->label(__('admin.review.reply'))->icon(Heroicon::OutlinedChatBubbleLeftRight)->color('gray')->iconButton()
                    ->tooltip(__('admin.review.reply'))
                    ->modalHeading(fn (Review $r) => __('admin.review.reply_to', ['name' => $r->name]))
                    ->modalDescription(fn (Review $r) => Str::limit($r->body, 300))
                    ->fillForm(fn (Review $r) => ['reply' => $r->reply])
                    ->schema([Textarea::make('reply')->label(__('admin.review.reply'))->rows(5)->required()])
                    ->action(function (Review $r, array $data) {
                        $r->update(['reply' => $data['reply'], 'is_approved' => true]);
                        Notification::make()->title(__('admin.notify.reply_saved'))->success()->send();
                    }),
                EditAction::make()->iconButton(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('approve')->label(__('admin.actions.approve'))->icon(Heroicon::OutlinedCheck)
                        ->action(fn (Collection $records) => Review::whereIn('id', $records->modelKeys())->update(['is_approved' => true]))
                        ->deselectRecordsAfterCompletion(),
                    BulkAction::make('reject')->label(__('admin.actions.reject'))->icon(Heroicon::OutlinedNoSymbol)
                        ->action(fn (Collection $records) => Review::whereIn('id', $records->modelKeys())->update(['is_approved' => false]))
                        ->deselectRecordsAfterCompletion(),
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
