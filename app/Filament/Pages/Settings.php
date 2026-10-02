<?php

namespace App\Filament\Pages;

use App\Filament\NavGroup;
use App\Models\Setting;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

/** Site-wide settings stored as key/value pairs in App\Models\Setting. */
class Settings extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCog6Tooth;

    protected static string|UnitEnum|null $navigationGroup = NavGroup::Settings;

    protected static ?int $navigationSort = 1;

    protected static ?string $slug = 'settings';

    /** @var array<string, mixed> */
    public ?array $data = [];

    public static function canAccess(): bool
    {
        return (bool) auth()->user()?->isAdmin();
    }

    public static function getNavigationLabel(): string
    {
        return __('admin.settings.title');
    }

    public function getTitle(): string
    {
        return __('admin.settings.title');
    }

    public function getSubheading(): ?string
    {
        return __('admin.settings.subheading');
    }

    public function mount(): void
    {
        Setting::flush();
        $this->form->fill(Setting::all_values());
    }

    public function form(Schema $schema): Schema
    {
        $text = fn (string $key) => TextInput::make($key)->label(__('admin.settings.'.$key));

        return $schema
            ->statePath('data')
            ->components([
                Tabs::make('settings')
                    ->persistTabInQueryString()
                    ->tabs([
                        Tab::make('general')->label(__('admin.settings.tab_general'))->icon(Heroicon::OutlinedBuildingOffice2)->schema([
                            Grid::make()->columns(['default' => 1, 'md' => 2])->schema([
                                $text('hotel_name')->required()->columnSpanFull(),
                                $text('hotel_tagline_en')->label(__('admin.settings.hotel_tagline').' (EN)'),
                                $text('hotel_tagline_ru')->label(__('admin.settings.hotel_tagline').' (RU)'),
                                $text('hotel_tagline_hy')->label(__('admin.settings.hotel_tagline').' (HY)'),
                                Select::make('hotel_stars')->label(__('admin.settings.hotel_stars'))->options(array_combine(range(1, 5), array_map(fn ($n) => str_repeat('★', $n), range(1, 5))))->native(false),
                            ]),
                        ]),
                        Tab::make('contacts')->label(__('admin.settings.tab_contacts'))->icon(Heroicon::OutlinedPhone)->schema([
                            Grid::make()->columns(['default' => 1, 'md' => 2])->schema([
                                $text('hotel_email')->email()->required()->prefixIcon(Heroicon::OutlinedEnvelope),
                                $text('hotel_phone')->tel()->telRegex('/^[0-9+\-\s().]{5,}$/')->prefixIcon(Heroicon::OutlinedPhone),
                                $text('hotel_address_en')->label(__('admin.settings.hotel_address').' (EN)'),
                                $text('hotel_address_ru')->label(__('admin.settings.hotel_address').' (RU)'),
                                $text('hotel_address_hy')->label(__('admin.settings.hotel_address').' (HY)'),
                                $text('map_lat')->numeric()->rule('between:-90,90'),
                                $text('map_lng')->numeric()->rule('between:-180,180'),
                            ]),
                        ]),
                        Tab::make('booking')->label(__('admin.settings.tab_booking'))->icon(Heroicon::OutlinedCalendarDays)->schema([
                            Grid::make()->columns(['default' => 1, 'md' => 3])->schema([
                                $text('currency')->required()->maxLength(3)->helperText('ISO 4217, e.g. USD'),
                                $text('currency_symbol')->required()->maxLength(4),
                                $text('tax_percent')->numeric()->minValue(0)->maxValue(50)->suffix('%')->required(),
                                $text('idram_amd_rate')->numeric()->minValue(1)->prefix('1 USD =')->suffix('AMD')->required(),
                                TimePicker::make('check_in_time')->label(__('admin.settings.check_in_time'))->seconds(false)->format('H:i')->native(false)->required(),
                                TimePicker::make('check_out_time')->label(__('admin.settings.check_out_time'))->seconds(false)->format('H:i')->native(false)->required(),
                                $text('free_cancellation_hours')->numeric()->minValue(0)->suffix(__('admin.settings.hours'))->required(),
                            ]),
                        ]),
                        Tab::make('social')->label(__('admin.settings.tab_social'))->icon(Heroicon::OutlinedShare)->schema([
                            Grid::make()->columns(['default' => 1, 'md' => 2])->schema([
                                $text('instagram')->url()->prefix('IG'),
                                $text('facebook')->url()->prefix('FB'),
                                $text('telegram')->url()->prefix('TG'),
                                $text('whatsapp')->helperText(__('admin.settings.whatsapp_hint'))->prefix('WA'),
                            ]),
                        ]),
                    ]),
            ]);
    }

    public function content(Schema $schema): Schema
    {
        return $schema->components([
            Form::make([EmbeddedSchema::make('form')])
                ->id('form')
                ->livewireSubmitHandler('save')
                ->footer([
                    Actions::make([
                        Action::make('save')->label(__('admin.settings.save'))->submit('save')->icon(Heroicon::OutlinedCheck)->keyBindings(['mod+s']),
                    ]),
                ]),
        ]);
    }

    public function save(): void
    {
        $data = $this->form->getState();

        foreach ($data as $key => $value) {
            if (array_key_exists($key, Setting::DEFAULTS)) {
                Setting::put($key, $value === null ? '' : (string) $value);
            }
        }
        Setting::flush();

        Notification::make()->title(__('admin.settings.saved'))->success()->send();
    }
}
