<?php

namespace App\Filament\Resources\Bookings\Schemas;

use App\Filament\Support\Ui;
use App\Models\Booking;
use App\Models\Extra;
use App\Models\PromoCode;
use App\Models\RoomType;
use App\Models\User;
use App\Services\BookingService;
use Carbon\CarbonImmutable;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Html;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Collection;

class BookingForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(['default' => 1, 'lg' => 3])
            ->components([
                Group::make([
                    static::staySection(),
                    static::guestSection(),
                    static::extrasSection(),
                ])->columnSpan(['lg' => 2]),
                Group::make([
                    Section::make(__('admin.booking.summary'))
                        ->icon(Heroicon::OutlinedCalculator)
                        ->schema([
                            Html::make(fn (Get $get, ?Booking $record) => static::quoteHtml($get, $record)),
                        ]),
                    Section::make(__('admin.booking.status_section'))
                        ->icon(Heroicon::OutlinedAdjustmentsHorizontal)
                        ->schema([
                            Select::make('status')
                                ->label(__('admin.fields.status'))
                                ->options(Ui::statusOptions())
                                ->default('confirmed')
                                ->native(false)
                                ->required(),
                            Select::make('payment_status')
                                ->label(__('admin.fields.payment_status'))
                                ->options(Ui::paymentStatusOptions())
                                ->native(false)
                                ->required()
                                ->visibleOn('edit'),
                            Select::make('payment_method')
                                ->label(__('admin.fields.payment_method'))
                                ->options(Ui::paymentMethodOptions())
                                ->default('on_arrival')
                                ->native(false)
                                ->required(),
                            Select::make('source')
                                ->label(__('admin.fields.source'))
                                ->options(Ui::sourceOptions())
                                ->default('admin')
                                ->native(false)
                                ->required(),
                            Select::make('locale')
                                ->label(__('admin.fields.guest_language'))
                                ->options(['en' => 'English', 'ru' => 'Русский'])
                                ->default(fn () => app()->getLocale())
                                ->native(false)
                                ->required(),
                        ]),
                    Section::make(__('admin.fields.internal_notes'))
                        ->icon(Heroicon::OutlinedLockClosed)
                        ->description(__('admin.booking.notes_hint'))
                        ->schema([
                            Textarea::make('internal_notes')->hiddenLabel()->rows(4),
                        ]),
                ])->columnSpan(['lg' => 1]),
            ]);
    }

    protected static function staySection(): Section
    {
        return Section::make(__('admin.booking.stay'))
            ->icon(Heroicon::OutlinedCalendarDays)
            ->columns(['default' => 1, 'sm' => 2, 'xl' => 4])
            ->schema([
                Select::make('room_type_id')
                    ->label(__('admin.fields.room_type'))
                    ->options(fn () => RoomType::orderBy('sort')->get()->mapWithKeys(fn (RoomType $t) => [$t->id => $t->name.' · '.money($t->base_price)]))
                    ->required()
                    ->native(false)
                    ->live()
                    ->afterStateUpdated(fn (Set $set) => $set('room_id', null))
                    ->columnSpan(['sm' => 2]),
                Select::make('room_id')
                    ->label(__('admin.fields.room'))
                    ->placeholder(__('admin.booking.auto_assign'))
                    ->options(fn (Get $get, ?Booking $record) => static::freeRoomOptions($get, $record))
                    ->native(false)
                    ->live()
                    ->helperText(fn (Get $get, ?Booking $record) => static::availabilityText($get, $record))
                    ->columnSpan(['sm' => 2]),
                DatePicker::make('check_in')
                    ->label(__('admin.fields.check_in'))
                    ->native(false)
                    ->displayFormat('d M Y')
                    ->closeOnDateSelection()
                    ->default(today())
                    ->required()
                    ->live()
                    ->afterStateUpdated(function (Get $get, Set $set, $state) {
                        if ($state && (! $get('check_out') || $get('check_out') <= $state)) {
                            $set('check_out', CarbonImmutable::parse($state)->addDay()->toDateString());
                        }
                    }),
                DatePicker::make('check_out')
                    ->label(__('admin.fields.check_out'))
                    ->native(false)
                    ->displayFormat('d M Y')
                    ->closeOnDateSelection()
                    ->default(today()->addDays(2))
                    ->after('check_in')
                    ->required()
                    ->live(),
                TextInput::make('adults')
                    ->label(__('admin.fields.adults'))
                    ->numeric()
                    ->minValue(1)
                    ->maxValue(fn (Get $get) => RoomType::find($get('room_type_id'))?->max_adults ?? 6)
                    ->default(2)
                    ->required()
                    ->live(onBlur: true),
                TextInput::make('children')
                    ->label(__('admin.fields.children'))
                    ->numeric()
                    ->minValue(0)
                    ->maxValue(fn (Get $get) => RoomType::find($get('room_type_id'))?->max_children ?? 4)
                    ->default(0)
                    ->required()
                    ->live(onBlur: true),
            ]);
    }

    protected static function guestSection(): Section
    {
        return Section::make(__('admin.booking.guest_details'))
            ->icon(Heroicon::OutlinedUser)
            ->columns(['default' => 1, 'sm' => 2])
            ->schema([
                Select::make('user_id')
                    ->label(__('admin.fields.guest_account'))
                    ->relationship('user', 'name', fn ($query) => $query->where('role', 'guest'))
                    ->getOptionLabelFromRecordUsing(fn (User $u) => "{$u->name} · {$u->email}")
                    ->searchable(['name', 'email'])
                    ->preload()
                    ->live()
                    ->afterStateUpdated(function ($state, Set $set, Get $get) {
                        $user = $state ? User::find($state) : null;
                        if (! $user) {
                            return;
                        }
                        [$first, $last] = array_pad(explode(' ', $user->name, 2), 2, '');
                        $set('first_name', $get('first_name') ?: $first);
                        $set('last_name', $get('last_name') ?: $last);
                        $set('email', $get('email') ?: $user->email);
                        $set('phone', $get('phone') ?: $user->phone);
                        $set('country', $get('country') ?: $user->country);
                    })
                    ->helperText(__('admin.booking.guest_account_hint'))
                    ->columnSpanFull(),
                TextInput::make('first_name')->label(__('admin.fields.first_name'))->required()->maxLength(100),
                TextInput::make('last_name')->label(__('admin.fields.last_name'))->required()->maxLength(100),
                TextInput::make('email')->label(__('admin.fields.email'))->email()->required()->prefixIcon(Heroicon::OutlinedEnvelope),
                TextInput::make('phone')->label(__('admin.fields.phone'))->tel()->telRegex('/^[0-9+\-\s().]{5,}$/')->prefixIcon(Heroicon::OutlinedPhone),
                TextInput::make('country')->label(__('admin.fields.country'))->datalist(['United States', 'United Kingdom', 'Germany', 'France', 'Italy', 'Russia', 'Kazakhstan', 'UAE', 'Turkey', 'Japan', 'Spain']),
                TimePicker::make('arrival_time')->label(__('admin.fields.arrival_time'))->seconds(false)->format('H:i')->native(false),
                Textarea::make('special_requests')->label(__('admin.fields.special_requests'))->rows(3)->columnSpanFull(),
            ]);
    }

    protected static function extrasSection(): Section
    {
        return Section::make(__('admin.booking.extras_promo'))
            ->icon(Heroicon::OutlinedGift)
            ->collapsible()
            ->schema([
                CheckboxList::make('extras')
                    ->label(__('admin.fields.extras'))
                    ->options(fn () => Extra::where('is_active', true)->orderBy('sort')->get()
                        ->mapWithKeys(fn (Extra $e) => [$e->id => $e->name]))
                    ->descriptions(fn () => Extra::where('is_active', true)->orderBy('sort')->get()
                        ->mapWithKeys(fn (Extra $e) => [$e->id => money($e->price).' · '.__('admin.pricing.'.$e->pricing)]))
                    ->columns(['default' => 1, 'sm' => 2])
                    ->live(),
                Select::make('promo_code_id')
                    ->label(__('admin.fields.promo_code'))
                    ->options(fn () => PromoCode::orderBy('code')->get()->mapWithKeys(fn (PromoCode $p) => [
                        $p->id => $p->code.' · '.($p->type === 'percent' ? (float) $p->value.'%' : money($p->value)).($p->is_active ? '' : ' ('.__('admin.misc.inactive').')'),
                    ]))
                    ->native(false)
                    ->searchable()
                    ->live(),
            ]);
    }

    /** @return array<int, string> */
    public static function freeRoomOptions(Get $get, ?Booking $record): array
    {
        return static::freeRooms($get, $record)
            ->mapWithKeys(fn ($room) => [$room->id => __('admin.booking.room_option', ['number' => $room->number, 'floor' => $room->floor])
                .($room->housekeeping !== 'clean' && $room->housekeeping !== 'inspected' ? ' · '.__('admin.housekeeping.'.$room->housekeeping) : '')])
            ->all();
    }

    protected static function freeRooms(Get $get, ?Booking $record): Collection
    {
        $type = RoomType::find($get('room_type_id'));
        $in = $get('check_in');
        $out = $get('check_out');
        if (! $type || ! $in || ! $out || $out <= $in) {
            return collect();
        }

        return app(BookingService::class)->freeRooms($type, $in, $out, $record?->id);
    }

    protected static function availabilityText(Get $get, ?Booking $record): ?string
    {
        if (! $get('room_type_id') || ! $get('check_in') || ! $get('check_out')) {
            return __('admin.booking.pick_type_dates');
        }
        $count = static::freeRooms($get, $record)->count();

        return $count
            ? trans_choice('admin.booking.free_rooms', $count, ['count' => $count])
            : __('admin.booking.no_rooms');
    }

    protected static function quoteHtml(Get $get, ?Booking $record): Htmlable|string
    {
        $type = RoomType::find($get('room_type_id'));
        $in = $get('check_in');
        $out = $get('check_out');
        $quote = null;
        if ($type && $in && $out && $out > $in) {
            $promo = $get('promo_code_id') ? PromoCode::find($get('promo_code_id'))?->code : null;
            $extras = collect($get('extras') ?? [])->mapWithKeys(fn ($id) => [(int) $id => 1])->all();
            $quote = app(BookingService::class)->quote($type, $in, $out, max(1, (int) $get('adults')), max(0, (int) $get('children')), $extras, $promo);
        }

        return view('filament.booking.quote-preview', [
            'quote' => $quote,
            'type' => $type,
            'record' => $record,
        ]);
    }

    /** Fill lifecycle timestamps that match the (manually chosen) status. */
    public static function stampStatus(Booking $booking): void
    {
        $now = now();
        $stamps = match ($booking->status) {
            'confirmed' => ['confirmed_at'],
            'checked_in' => ['confirmed_at', 'checked_in_at'],
            'checked_out' => ['confirmed_at', 'checked_in_at', 'checked_out_at'],
            'cancelled' => ['cancelled_at'],
            default => [],
        };
        foreach ($stamps as $column) {
            $booking->{$column} ??= $now;
        }
        if ($booking->isDirty()) {
            $booking->save();
        }
    }
}
