<?php

namespace App\Filament\Resources\Bookings;

use App\Filament\Support\Ui;
use App\Mail\BookingConfirmed;
use App\Models\Booking;
use App\Services\BookingService;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

/** Booking lifecycle actions shared by the table, view page and dashboard widgets. */
class BookingActions
{
    /** @return array<Action> */
    public static function all(): array
    {
        return [
            static::confirm(),
            static::checkIn(),
            static::checkOut(),
            static::recordPayment(),
            static::sendConfirmation(),
            static::invoice(),
            static::cancel(),
        ];
    }

    public static function group(): ActionGroup
    {
        return ActionGroup::make(static::all())
            ->label(__('admin.actions.manage'))
            ->icon(Heroicon::EllipsisVertical)
            ->color('gray');
    }

    public static function confirm(): Action
    {
        return Action::make('confirm')
            ->label(__('admin.actions.confirm'))
            ->icon(Heroicon::OutlinedCheckBadge)
            ->color('info')
            ->visible(fn (Booking $record) => $record->status === 'pending')
            ->requiresConfirmation()
            ->modalDescription(__('admin.actions.confirm_desc'))
            ->action(function (Booking $record, $livewire) {
                app(BookingService::class)->confirm($record);
                static::done(__('admin.notify.confirmed', ['ref' => $record->reference]));
                $livewire?->dispatch('booking-updated');
            });
    }

    public static function checkIn(): Action
    {
        return Action::make('checkIn')
            ->label(__('admin.actions.check_in'))
            ->icon(Heroicon::OutlinedKey)
            ->color('success')
            ->visible(fn (Booking $record) => in_array($record->status, ['pending', 'confirmed'], true)
                && $record->check_in->lte(today()) && $record->check_out->gte(today()))
            ->requiresConfirmation()
            ->modalIcon(Heroicon::OutlinedKey)
            ->modalDescription(fn (Booking $record) => __('admin.actions.check_in_desc', [
                'guest' => $record->guest_name,
                'room' => $record->room?->number ?? '—',
            ]))
            ->action(function (Booking $record, $livewire) {
                app(BookingService::class)->checkIn($record);
                static::done(__('admin.notify.checked_in', ['guest' => $record->guest_name]));
                $livewire?->dispatch('booking-updated');
            });
    }

    public static function checkOut(): Action
    {
        return Action::make('checkOut')
            ->label(__('admin.actions.check_out'))
            ->icon(Heroicon::OutlinedArrowRightStartOnRectangle)
            ->color('gray')
            ->visible(fn (Booking $record) => $record->status === 'checked_in')
            ->requiresConfirmation()
            ->modalDescription(fn (Booking $record) => $record->balance > 0
                ? __('admin.actions.check_out_balance', ['amount' => money($record->balance, true)])
                : __('admin.actions.check_out_desc'))
            ->action(function (Booking $record, $livewire) {
                app(BookingService::class)->checkOut($record);
                static::done(__('admin.notify.checked_out', ['guest' => $record->guest_name]));
                $livewire?->dispatch('booking-updated');
            });
    }

    public static function cancel(): Action
    {
        return Action::make('cancel')
            ->label(__('admin.actions.cancel'))
            ->icon(Heroicon::OutlinedXCircle)
            ->color('danger')
            ->visible(fn (Booking $record) => in_array($record->status, ['pending', 'confirmed'], true))
            ->requiresConfirmation()
            ->modalDescription(__('admin.actions.cancel_desc'))
            ->action(function (Booking $record, $livewire) {
                app(BookingService::class)->cancel($record);
                static::done(__('admin.notify.cancelled', ['ref' => $record->reference]), 'warning');
                $livewire?->dispatch('booking-updated');
            });
    }

    public static function recordPayment(): Action
    {
        return Action::make('recordPayment')
            ->label(__('admin.actions.record_payment'))
            ->icon(Heroicon::OutlinedBanknotes)
            ->color('primary')
            ->visible(fn (Booking $record) => $record->balance > 0 && ! in_array($record->status, ['cancelled', 'no_show'], true))
            ->modalHeading(fn (Booking $record) => __('admin.actions.record_payment').' · '.$record->reference)
            ->modalDescription(fn (Booking $record) => __('admin.actions.balance_due', ['amount' => money($record->balance, true)]))
            ->modalWidth('md')
            ->fillForm(fn (Booking $record) => ['amount' => $record->balance, 'method' => 'card'])
            ->schema([
                TextInput::make('amount')
                    ->label(__('admin.fields.amount'))
                    ->numeric()
                    ->minValue(0.01)
                    ->prefix(setting('currency_symbol', '$'))
                    ->required(),
                Select::make('method')
                    ->label(__('admin.fields.payment_method'))
                    ->options(Ui::options('payment_method', ['card', 'idram', 'cash', 'bank_transfer']))
                    ->native(false)
                    ->required(),
                TextInput::make('transaction_id')
                    ->label(__('admin.fields.transaction_id'))
                    ->placeholder(__('admin.fields.optional')),
            ])
            ->action(function (Booking $record, array $data, $livewire) {
                $amount = round((float) $data['amount'], 2);
                $record->payments()->create([
                    'amount' => $amount,
                    'method' => $data['method'],
                    'status' => 'succeeded',
                    'transaction_id' => $data['transaction_id'] ?: 'desk_'.Str::lower(Str::random(10)),
                ]);
                app(BookingService::class)->registerPayment($record, $amount);
                static::done(__('admin.notify.payment_recorded', ['amount' => money($amount, true)]));
                $livewire?->dispatch('booking-updated');
            });
    }

    public static function invoice(): Action
    {
        return Action::make('invoice')
            ->label(__('admin.actions.invoice'))
            ->icon(Heroicon::OutlinedDocumentArrowDown)
            ->color('gray')
            ->url(fn (Booking $record) => route('booking.invoice', $record))
            ->openUrlInNewTab();
    }

    public static function sendConfirmation(): Action
    {
        return Action::make('sendConfirmation')
            ->label(__('admin.actions.send_email'))
            ->icon(Heroicon::OutlinedEnvelope)
            ->color('gray')
            ->visible(fn (Booking $record) => class_exists(BookingConfirmed::class)
                && in_array($record->status, ['confirmed', 'checked_in', 'pending'], true))
            ->requiresConfirmation()
            ->modalDescription(fn (Booking $record) => __('admin.actions.send_email_desc', ['email' => $record->email]))
            ->action(function (Booking $record, $livewire) {
                try {
                    Mail::to($record->email)->locale($record->locale ?: 'en')->send(new BookingConfirmed($record));
                    static::done(__('admin.notify.email_sent', ['email' => $record->email]));
                    $livewire?->dispatch('booking-updated');
                } catch (\Throwable $e) {
                    Log::warning('Booking email failed: '.$e->getMessage());
                    Notification::make()->title(__('admin.notify.email_failed'))->body($e->getMessage())->danger()->send();
                }
            });
    }

    protected static function done(string $title, string $type = 'success'): void
    {
        Notification::make()->title($title)->{$type}()->send();
    }
}
