<?php

namespace App\Filament\Resources\Bookings\RelationManagers;

use App\Filament\Resources\Bookings\BookingActions;
use App\Filament\Support\Ui;
use App\Models\Payment;
use App\Services\BookingService;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Livewire\Attributes\On;

class PaymentsRelationManager extends RelationManager
{
    protected static string $relationship = 'payments';

    protected static bool $isLazy = false;

    protected static string|\BackedEnum|null $icon = Heroicon::OutlinedBanknotes;

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('admin.payment.plural');
    }

    #[On('booking-updated')]
    public function refreshPayments(): void
    {
        // Re-render picks up new payments.
    }

    public function isReadOnly(): bool
    {
        return false;
    }

    public function table(Table $table): Table
    {
        $currency = Ui::currency();

        return $table
            ->recordTitleAttribute('transaction_id')
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('created_at')->label(__('admin.fields.date'))->dateTime('d M Y, H:i')->sortable(),
                TextColumn::make('amount')->label(__('admin.fields.amount'))->money($currency)->weight('semibold'),
                TextColumn::make('method')->label(__('admin.fields.payment_method'))->badge()->color('gray')
                    ->formatStateUsing(fn ($state) => __('admin.payment_method.'.$state)),
                TextColumn::make('card_last4')->label(__('admin.fields.card'))
                    ->formatStateUsing(fn (Payment $r) => $r->card_last4 ? ($r->card_brand ?? 'Card').' •••• '.$r->card_last4 : null)
                    ->placeholder('—'),
                TextColumn::make('status')->label(__('admin.fields.status'))->badge()
                    ->formatStateUsing(fn ($state) => __('admin.payment_result.'.$state))
                    ->color(fn ($state) => ['succeeded' => 'success', 'failed' => 'danger', 'refunded' => 'gray'][$state] ?? 'gray'),
                TextColumn::make('transaction_id')->label(__('admin.fields.transaction_id'))->fontFamily('mono')->copyable()->size('xs')->color('gray'),
            ])
            ->headerActions([
                BookingActions::recordPayment()
                    ->record(fn () => $this->getOwnerRecord())
                    ->after(fn () => $this->dispatch('booking-updated')),
            ])
            ->recordActions([
                Action::make('refund')
                    ->label(__('admin.actions.refund'))
                    ->icon(Heroicon::OutlinedArrowUturnLeft)
                    ->color('danger')
                    ->visible(fn (Payment $record) => $record->status === 'succeeded' && auth()->user()?->isManager())
                    ->requiresConfirmation()
                    ->action(function (Payment $record) {
                        $record->update(['status' => 'refunded']);
                        app(BookingService::class)->registerPayment($record->booking, -1 * (float) $record->amount);
                        $this->dispatch('booking-updated');
                        Notification::make()->title(__('admin.notify.refunded'))->success()->send();
                    }),
            ])
            ->emptyStateHeading(__('admin.payment.empty'))
            ->emptyStateIcon(Heroicon::OutlinedBanknotes)
            ->paginated(false);
    }
}
