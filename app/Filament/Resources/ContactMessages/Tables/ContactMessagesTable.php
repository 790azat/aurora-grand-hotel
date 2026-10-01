<?php

namespace App\Filament\Resources\ContactMessages\Tables;

use App\Models\ContactMessage;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ViewAction;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;

class ContactMessagesTable
{
    public static function configure(Table $table): Table
    {
        $manager = fn () => (bool) auth()->user()?->isManager();

        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                IconColumn::make('is_read')->label('')
                    ->icon(fn ($state) => $state ? 'heroicon-o-envelope-open' : 'heroicon-s-envelope')
                    ->color(fn ($state) => $state ? 'gray' : 'primary')
                    ->width('1%'),
                TextColumn::make('name')->label(__('admin.fields.from'))->searchable(['name', 'email'])
                    ->weight(fn (ContactMessage $m) => $m->is_read ? 'normal' : 'bold')
                    ->description(fn (ContactMessage $m) => $m->email),
                TextColumn::make('subject')->label(__('admin.fields.subject'))
                    ->formatStateUsing(fn (ContactMessage $m) => ($m->subject ?: __('admin.inbox.no_subject')))
                    ->description(fn (ContactMessage $m) => Str::limit($m->message, 90))
                    ->weight(fn (ContactMessage $m) => $m->is_read ? 'normal' : 'bold')
                    ->wrap()->searchable(['subject', 'message']),
                TextColumn::make('created_at')->label(__('admin.fields.received'))->since()->dateTimeTooltip()->sortable(),
            ])
            ->recordActions([
                ViewAction::make()->iconButton()
                    ->modalHeading(fn (ContactMessage $m) => $m->subject ?: __('admin.inbox.no_subject'))
                    ->after(fn (ContactMessage $m) => $m->is_read ?: $m->update(['is_read' => true]))
                    ->mountUsing(fn (ContactMessage $m) => $m->is_read ?: $m->update(['is_read' => true]))
                    ->extraModalFooterActions(fn (ContactMessage $m) => [
                        Action::make('reply')->label(__('admin.inbox.reply_email'))->icon(Heroicon::OutlinedPaperAirplane)
                            ->url('mailto:'.$m->email.'?subject='.rawurlencode('Re: '.($m->subject ?: 'Aurora Grand'))),
                    ]),
                Action::make('toggleRead')->iconButton()
                    ->label(fn (ContactMessage $m) => $m->is_read ? __('admin.inbox.mark_unread') : __('admin.inbox.mark_read'))
                    ->tooltip(fn (ContactMessage $m) => $m->is_read ? __('admin.inbox.mark_unread') : __('admin.inbox.mark_read'))
                    ->icon(fn (ContactMessage $m) => $m->is_read ? Heroicon::OutlinedEnvelope : Heroicon::OutlinedEnvelopeOpen)
                    ->color('gray')
                    ->action(fn (ContactMessage $m) => $m->update(['is_read' => ! $m->is_read])),
                DeleteAction::make()->iconButton()->visible($manager),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('markRead')->label(__('admin.inbox.mark_read'))->icon(Heroicon::OutlinedEnvelopeOpen)
                        ->action(fn (Collection $records) => ContactMessage::whereIn('id', $records->modelKeys())->update(['is_read' => true]))
                        ->deselectRecordsAfterCompletion(),
                    DeleteBulkAction::make()->visible($manager),
                ]),
            ])
            ->emptyStateHeading(__('admin.inbox.empty'))
            ->emptyStateIcon(Heroicon::OutlinedInbox);
    }
}
