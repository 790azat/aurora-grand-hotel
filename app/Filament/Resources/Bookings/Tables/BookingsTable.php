<?php

namespace App\Filament\Resources\Bookings\Tables;

use App\Filament\Resources\Bookings\BookingActions;
use App\Filament\Resources\Bookings\BookingResource;
use App\Filament\Support\Ui;
use App\Models\Booking;
use App\Models\RoomType;
use App\Services\BookingService;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\Column;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;

class BookingsTable
{
    public static function configure(Table $table): Table
    {
        $currency = Ui::currency();

        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->with(['roomType', 'room']))
            ->defaultSort('check_in', 'desc')
            ->striped()
            ->defaultPaginationPageOption(25)
            ->columns(static::columns($currency))
            ->filters([
                SelectFilter::make('status')
                    ->label(__('admin.fields.status'))
                    ->options(Ui::statusOptions())
                    ->multiple(),
                SelectFilter::make('payment_status')
                    ->label(__('admin.fields.payment_status'))
                    ->options(Ui::paymentStatusOptions()),
                SelectFilter::make('source')
                    ->label(__('admin.fields.source'))
                    ->options(Ui::sourceOptions())
                    ->multiple(),
                SelectFilter::make('room_type_id')
                    ->label(__('admin.fields.room_type'))
                    ->options(fn () => RoomType::orderBy('sort')->get()->pluck('name', 'id'))
                    ->multiple(),
                Filter::make('arrivals')
                    ->label(__('admin.filters.arrivals_between'))
                    ->schema([
                        DatePicker::make('from')->label(__('admin.filters.arrival_from'))->native(false),
                        DatePicker::make('until')->label(__('admin.filters.arrival_until'))->native(false),
                    ])
                    ->columns(2)
                    ->query(fn (Builder $query, array $data) => $query
                        ->when($data['from'] ?? null, fn ($q, $d) => $q->whereDate('check_in', '>=', $d))
                        ->when($data['until'] ?? null, fn ($q, $d) => $q->whereDate('check_in', '<=', $d)))
                    ->indicateUsing(function (array $data): array {
                        $out = [];
                        if ($data['from'] ?? null) {
                            $out[] = __('admin.filters.arrival_from').': '.Carbon::parse($data['from'])->isoFormat('D MMM YYYY');
                        }
                        if ($data['until'] ?? null) {
                            $out[] = __('admin.filters.arrival_until').': '.Carbon::parse($data['until'])->isoFormat('D MMM YYYY');
                        }

                        return $out;
                    })
                    ->columnSpan(2),
                TernaryFilter::make('balance')
                    ->label(__('admin.filters.balance_due'))
                    ->queries(
                        true: fn (Builder $q) => $q->whereColumn('amount_paid', '<', 'total')->whereNotIn('status', ['cancelled', 'no_show']),
                        false: fn (Builder $q) => $q->whereColumn('amount_paid', '>=', 'total'),
                    ),
            ])
            ->filtersFormColumns(2)
            ->recordUrl(fn (Booking $record) => BookingResource::getUrl('view', ['record' => $record]))
            ->recordActions([
                ViewAction::make()->iconButton(),
                EditAction::make()->iconButton(),
                BookingActions::group(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('markConfirmed')
                        ->label(__('admin.actions.bulk_confirm'))
                        ->icon(Heroicon::OutlinedCheckBadge)
                        ->requiresConfirmation()
                        ->action(function (Collection $records) {
                            $n = 0;
                            foreach ($records as $r) {
                                if ($r->status === 'pending') {
                                    app(BookingService::class)->confirm($r);
                                    $n++;
                                }
                            }
                            Notification::make()->title(__('admin.notify.bulk_confirmed', ['count' => $n]))->success()->send();
                        })
                        ->deselectRecordsAfterCompletion(),
                    DeleteBulkAction::make()->visible(fn () => (bool) auth()->user()?->isManager()),
                ]),
            ])
            ->emptyStateIcon(Heroicon::OutlinedCalendarDays)
            ->emptyStateHeading(__('admin.booking.empty'));
    }

    /** @return array<Column> */
    public static function columns(string $currency, bool $compact = false): array
    {
        return [
            TextColumn::make('reference')
                ->label(__('admin.fields.reference'))
                ->searchable()
                ->sortable()
                ->copyable()
                ->weight('bold')
                ->fontFamily('mono')
                ->color('primary'),
            TextColumn::make('guest_name')
                ->label(__('admin.fields.guest'))
                ->searchable(['first_name', 'last_name', 'email', 'phone'])
                ->sortable(['last_name', 'first_name'])
                ->description(fn (Booking $r) => $r->email)
                ->wrap(),
            TextColumn::make('roomType.name')
                ->label(__('admin.fields.room_type'))
                ->badge()
                ->color('gray')
                ->toggleable(),
            TextColumn::make('room.number')
                ->label(__('admin.fields.room'))
                ->placeholder('—')
                ->icon(Heroicon::OutlinedKey)
                ->sortable()
                ->toggleable(),
            TextColumn::make('check_in')
                ->label(__('admin.fields.check_in'))
                ->date('d M Y')
                ->sortable()
                ->description(fn (Booking $r) => '→ '.$r->check_out->isoFormat('D MMM').' · '.trans_choice('admin.booking.nights_x', $r->nights, ['count' => $r->nights])),
            TextColumn::make('check_out')
                ->label(__('admin.fields.check_out'))
                ->date('d M Y')
                ->sortable()
                ->toggleable(isToggledHiddenByDefault: true),
            TextColumn::make('nights')
                ->label(__('admin.fields.nights'))
                ->numeric()
                ->alignCenter()
                ->sortable()
                ->toggleable(isToggledHiddenByDefault: true),
            TextColumn::make('adults')
                ->label(__('admin.fields.guests'))
                ->formatStateUsing(fn (Booking $r) => $r->adults.($r->children ? ' + '.$r->children : ''))
                ->icon(Heroicon::OutlinedUsers)
                ->alignCenter()
                ->toggleable(isToggledHiddenByDefault: true),
            TextColumn::make('total')
                ->label(__('admin.fields.total'))
                ->money($currency)
                ->sortable()
                ->alignEnd()
                ->weight('semibold'),
            TextColumn::make('amount_paid')
                ->label(__('admin.fields.paid'))
                ->money($currency)
                ->sortable()
                ->alignEnd()
                ->color(fn (Booking $r) => $r->balance > 0 ? 'warning' : 'success')
                ->toggleable(),
            TextColumn::make('status')
                ->label(__('admin.fields.status'))
                ->badge()
                ->formatStateUsing(fn ($state) => __('admin.status.'.$state))
                ->color(fn ($state) => Ui::STATUS_COLORS[$state] ?? 'gray')
                ->icon(fn ($state) => Ui::STATUS_ICONS[$state] ?? null)
                ->sortable(),
            TextColumn::make('payment_status')
                ->label(__('admin.fields.payment'))
                ->badge()
                ->formatStateUsing(fn ($state) => __('admin.payment_status.'.$state))
                ->color(fn ($state) => Ui::PAYMENT_COLORS[$state] ?? 'gray')
                ->sortable()
                ->toggleable(),
            TextColumn::make('source')
                ->label(__('admin.fields.source'))
                ->formatStateUsing(fn ($state) => __('admin.source.'.$state))
                ->badge()
                ->color('gray')
                ->toggleable(isToggledHiddenByDefault: true),
            TextColumn::make('created_at')
                ->label(__('admin.fields.created_at'))
                ->since()
                ->dateTimeTooltip()
                ->sortable()
                ->toggleable(isToggledHiddenByDefault: true),
        ];
    }
}
