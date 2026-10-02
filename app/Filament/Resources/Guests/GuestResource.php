<?php

namespace App\Filament\Resources\Guests;

use App\Filament\NavGroup;
use App\Filament\Support\Ui;
use App\Http\Middleware\SetLocale;
use App\Models\User;
use BackedEnum;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use UnitEnum;

class GuestResource extends Resource
{
    protected static ?string $model = User::class;

    protected static bool $hasTitleCaseModelLabel = false;

    protected static ?string $slug = 'guests';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUserGroup;

    protected static string|UnitEnum|null $navigationGroup = NavGroup::Guests;

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'name';

    public static function getModelLabel(): string
    {
        return __('admin.models.guest.singular');
    }

    public static function getPluralModelLabel(): string
    {
        return __('admin.models.guest.plural');
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['name', 'email', 'phone'];
    }

    public static function getGlobalSearchResultDetails(Model $record): array
    {
        return [__('admin.fields.email') => $record->email];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('role', 'guest')
            ->withCount(['bookings as stays_count' => fn ($q) => $q->where('status', 'checked_out')])
            ->withSum(['bookings as spent_sum' => fn ($q) => $q->whereNotIn('status', ['cancelled'])], 'amount_paid')
            ->withMax('bookings as last_stay', 'check_in');
    }

    public static function canDelete(Model $record): bool
    {
        return (bool) auth()->user()?->isManager();
    }

    public static function canDeleteAny(): bool
    {
        return (bool) auth()->user()?->isManager();
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make()->columns(['default' => 1, 'sm' => 2])->columnSpanFull()->schema([
                TextInput::make('name')->label(__('admin.fields.name'))->required()->maxLength(255),
                TextInput::make('email')->label(__('admin.fields.email'))->email()->required()->unique(ignoreRecord: true),
                TextInput::make('phone')->label(__('admin.fields.phone'))->tel()->telRegex('/^[0-9+\-\s().]{5,}$/'),
                TextInput::make('country')->label(__('admin.fields.country')),
                Select::make('locale')->label(__('admin.fields.language'))->options(SetLocale::LOCALES)->default('en')->native(false),
                TextInput::make('password')->label(__('admin.fields.password'))->password()->revealable()
                    ->required(fn (string $operation) => $operation === 'create')
                    ->dehydrated(fn ($state) => filled($state))->minLength(8)
                    ->helperText(fn (string $operation) => $operation === 'edit' ? __('admin.staff.password_hint') : null),
            ]),
        ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema->components([
            Section::make()->columns(['default' => 2, 'md' => 4])->columnSpanFull()->schema([
                TextEntry::make('name')->label(__('admin.fields.name'))->weight('semibold'),
                TextEntry::make('email')->label(__('admin.fields.email'))->copyable()->url(fn (User $u) => 'mailto:'.$u->email)->color('primary'),
                TextEntry::make('phone')->label(__('admin.fields.phone'))->placeholder('—'),
                TextEntry::make('country')->label(__('admin.fields.country'))->placeholder('—'),
                TextEntry::make('stays_count')->label(__('admin.guest.stays'))->numeric()->size('lg')->weight('bold'),
                TextEntry::make('spent_sum')->label(__('admin.guest.total_spent'))->money(Ui::currency())->size('lg')->weight('bold')->color('primary'),
                TextEntry::make('last_stay')->label(__('admin.guest.last_stay'))->date('d M Y')->placeholder('—'),
                TextEntry::make('created_at')->label(__('admin.guest.member_since'))->date('d M Y'),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('name')->label(__('admin.fields.name'))->searchable(['name', 'email', 'phone'])->sortable()->weight('semibold')
                    ->description(fn (User $u) => $u->email),
                TextColumn::make('phone')->label(__('admin.fields.phone'))->placeholder('—')->toggleable(),
                TextColumn::make('country')->label(__('admin.fields.country'))->placeholder('—')->sortable()->toggleable(),
                TextColumn::make('stays_count')->label(__('admin.guest.stays'))->badge()->color('gray')->sortable()->alignCenter(),
                TextColumn::make('spent_sum')->label(__('admin.guest.total_spent'))->money(Ui::currency())->sortable()->placeholder(money(0)),
                TextColumn::make('last_stay')->label(__('admin.guest.last_stay'))->date('d M Y')->placeholder('—')->sortable(),
                TextColumn::make('locale')->label(__('admin.fields.language'))->badge()->color('gray')->formatStateUsing(fn ($s) => strtoupper((string) $s))->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')->label(__('admin.guest.member_since'))->date('d M Y')->sortable()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Filter::make('returning')->label(__('admin.guest.returning'))->query(fn (Builder $q) => $q->has('bookings', '>=', 2)),
            ])
            ->recordActions([ViewAction::make(), EditAction::make()]);
    }

    public static function getRelations(): array
    {
        return [RelationManagers\BookingsRelationManager::class];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListGuests::route('/'),
            'create' => Pages\CreateGuest::route('/create'),
            'view' => Pages\ViewGuest::route('/{record}'),
            'edit' => Pages\EditGuest::route('/{record}/edit'),
        ];
    }
}
