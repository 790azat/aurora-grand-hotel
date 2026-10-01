<?php

namespace App\Filament\Resources\Bookings\Schemas;

use App\Filament\Resources\Guests\GuestResource;
use App\Filament\Support\Ui;
use App\Models\Booking;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\ViewEntry;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class BookingInfolist
{
    public static function configure(Schema $schema): Schema
    {
        $currency = Ui::currency();

        return $schema
            ->columns(['default' => 1, 'lg' => 3])
            ->components([
                Group::make([
                    Section::make(__('admin.booking.stay'))
                        ->icon(Heroicon::OutlinedCalendarDays)
                        ->columns(['default' => 2, 'md' => 4])
                        ->schema([
                            TextEntry::make('roomType.name')->label(__('admin.fields.room_type'))->weight('semibold'),
                            TextEntry::make('room.number')->label(__('admin.fields.room'))->placeholder(__('admin.booking.unassigned'))
                                ->icon(Heroicon::OutlinedKey)
                                ->helperText(fn (Booking $r) => $r->room ? __('admin.booking.floor', ['floor' => $r->room->floor]) : null),
                            TextEntry::make('check_in')->label(__('admin.fields.check_in'))->date('D, d M Y')
                                ->helperText(fn () => __('admin.booking.from_time', ['time' => setting('check_in_time')])),
                            TextEntry::make('check_out')->label(__('admin.fields.check_out'))->date('D, d M Y')
                                ->helperText(fn () => __('admin.booking.until_time', ['time' => setting('check_out_time')])),
                            TextEntry::make('nights')->label(__('admin.fields.nights')),
                            TextEntry::make('adults')->label(__('admin.fields.guests'))
                                ->formatStateUsing(fn (Booking $r) => trans_choice('admin.booking.adults_x', $r->adults, ['count' => $r->adults])
                                    .($r->children ? ', '.trans_choice('admin.booking.children_x', $r->children, ['count' => $r->children]) : '')),
                            TextEntry::make('source')->label(__('admin.fields.source'))->badge()->color('gray')
                                ->formatStateUsing(fn ($state) => __('admin.source.'.$state)),
                            TextEntry::make('created_at')->label(__('admin.fields.booked_on'))->dateTime('d M Y, H:i'),
                        ]),
                    Section::make(__('admin.booking.guest_details'))
                        ->icon(Heroicon::OutlinedUser)
                        ->columns(['default' => 1, 'md' => 3])
                        ->schema([
                            TextEntry::make('guest_name')->label(__('admin.fields.guest'))->weight('semibold'),
                            TextEntry::make('email')->label(__('admin.fields.email'))->copyable()->icon(Heroicon::OutlinedEnvelope)
                                ->url(fn (Booking $r) => 'mailto:'.$r->email),
                            TextEntry::make('phone')->label(__('admin.fields.phone'))->placeholder('—')->icon(Heroicon::OutlinedPhone)
                                ->url(fn (Booking $r) => $r->phone ? 'tel:'.preg_replace('/[^\d+]/', '', $r->phone) : null),
                            TextEntry::make('country')->label(__('admin.fields.country'))->placeholder('—'),
                            TextEntry::make('arrival_time')->label(__('admin.fields.arrival_time'))->placeholder('—'),
                            TextEntry::make('user.name')->label(__('admin.fields.guest_account'))->placeholder(__('admin.booking.no_account'))
                                ->url(fn (Booking $r) => $r->user_id ? GuestResource::getUrl('view', ['record' => $r->user_id]) : null)
                                ->color('primary'),
                            TextEntry::make('special_requests')->label(__('admin.fields.special_requests'))->placeholder('—')->columnSpanFull(),
                        ]),
                    Section::make(__('admin.booking.price_breakdown'))
                        ->icon(Heroicon::OutlinedReceiptPercent)
                        ->schema([
                            ViewEntry::make('price')->hiddenLabel()->view('filament.booking.price-breakdown'),
                        ]),
                ])->columnSpan(['lg' => 2]),
                Group::make([
                    Section::make(__('admin.booking.status_section'))
                        ->icon(Heroicon::OutlinedAdjustmentsHorizontal)
                        ->columns(2)
                        ->schema([
                            TextEntry::make('status')->label(__('admin.fields.status'))->badge()
                                ->formatStateUsing(fn ($state) => __('admin.status.'.$state))
                                ->color(fn ($state) => Ui::STATUS_COLORS[$state] ?? 'gray')
                                ->icon(fn ($state) => Ui::STATUS_ICONS[$state] ?? null),
                            TextEntry::make('payment_status')->label(__('admin.fields.payment'))->badge()
                                ->formatStateUsing(fn ($state) => __('admin.payment_status.'.$state))
                                ->color(fn ($state) => Ui::PAYMENT_COLORS[$state] ?? 'gray'),
                            TextEntry::make('total')->label(__('admin.fields.total'))->money($currency)->weight('bold')->size('lg'),
                            TextEntry::make('balance')->label(__('admin.fields.balance'))->money($currency)->weight('bold')->size('lg')
                                ->color(fn ($state) => $state > 0 ? 'warning' : 'success'),
                            TextEntry::make('payment_method')->label(__('admin.fields.payment_method'))
                                ->formatStateUsing(fn ($state) => __('admin.payment_method.'.$state)),
                            TextEntry::make('reference')->label(__('admin.fields.reference'))->copyable()->fontFamily('mono'),
                        ]),
                    Section::make(__('admin.booking.timeline'))
                        ->icon(Heroicon::OutlinedClock)
                        ->schema([
                            ViewEntry::make('timeline')->hiddenLabel()->view('filament.booking.timeline'),
                        ]),
                    Section::make(__('admin.fields.internal_notes'))
                        ->icon(Heroicon::OutlinedLockClosed)
                        ->schema([
                            TextEntry::make('internal_notes')->hiddenLabel()->placeholder(__('admin.booking.no_notes'))->markdown(),
                        ]),
                ])->columnSpan(['lg' => 1]),
            ]);
    }
}
