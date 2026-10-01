<?php

namespace App\Filament\Resources\Subscribers\Tables;

use App\Models\Subscriber;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class SubscribersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('email')->label(__('admin.fields.email'))->searchable()->copyable()->weight('semibold')->icon(Heroicon::OutlinedEnvelope),
                TextColumn::make('locale')->label(__('admin.fields.language'))->badge()->color('gray')->formatStateUsing(fn ($state) => strtoupper($state)),
                TextColumn::make('created_at')->label(__('admin.fields.subscribed_at'))->date('d M Y')->sortable(),
            ])
            ->filters([
                SelectFilter::make('locale')->label(__('admin.fields.language'))->options(['en' => 'English', 'ru' => 'Русский']),
            ])
            ->headerActions([
                Action::make('export')
                    ->label(__('admin.actions.export_csv'))
                    ->icon(Heroicon::OutlinedArrowDownTray)
                    ->color('gray')
                    ->action(fn () => response()->streamDownload(function () {
                        $out = fopen('php://output', 'w');
                        fputcsv($out, ['email', 'locale', 'subscribed_at']);
                        Subscriber::orderBy('created_at')->each(fn (Subscriber $s) => fputcsv($out, [$s->email, $s->locale, $s->created_at?->toDateTimeString()]));
                        fclose($out);
                    }, 'subscribers-'.now()->format('Y-m-d').'.csv', ['Content-Type' => 'text/csv'])),
            ])
            ->recordActions([EditAction::make()->iconButton(), DeleteAction::make()->iconButton()])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }
}
