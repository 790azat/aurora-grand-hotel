<?php

namespace App\Filament\Resources\Staff;

use App\Filament\Concerns\AdminOnly;
use App\Filament\NavGroup;
use App\Filament\Support\Ui;
use App\Http\Middleware\SetLocale;
use App\Models\User;
use BackedEnum;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use UnitEnum;

class StaffResource extends Resource
{
    use AdminOnly;

    protected static ?string $model = User::class;

    protected static bool $hasTitleCaseModelLabel = false;

    protected static ?string $slug = 'staff';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedShieldCheck;

    protected static string|UnitEnum|null $navigationGroup = NavGroup::Settings;

    protected static ?int $navigationSort = 2;

    protected static ?string $recordTitleAttribute = 'name';

    protected static bool $isGloballySearchable = false;

    public const STAFF_ROLES = ['admin', 'manager', 'reception'];

    public static function getModelLabel(): string
    {
        return __('admin.models.staff.singular');
    }

    public static function getPluralModelLabel(): string
    {
        return __('admin.models.staff.plural');
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->whereIn('role', self::STAFF_ROLES);
    }

    public static function canDelete(Model $record): bool
    {
        return auth()->user()?->isAdmin() && $record->getKey() !== auth()->id();
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make()->columns(['default' => 1, 'sm' => 2])->columnSpanFull()->schema([
                TextInput::make('name')->label(__('admin.fields.name'))->required()->maxLength(255),
                TextInput::make('email')->label(__('admin.fields.email'))->email()->required()->unique(ignoreRecord: true),
                TextInput::make('phone')->label(__('admin.fields.phone'))->tel()->telRegex('/^[0-9+\-\s().]{5,}$/'),
                Select::make('role')->label(__('admin.fields.role'))->options(Ui::options('roles', self::STAFF_ROLES))
                    ->required()->native(false)->default('reception')
                    ->disabled(fn (?User $record) => $record?->getKey() === auth()->id())
                    ->helperText(__('admin.staff.role_hint')),
                TextInput::make('password')->label(__('admin.fields.password'))->password()->revealable()
                    ->required(fn (string $operation) => $operation === 'create')
                    ->dehydrated(fn ($state) => filled($state))
                    ->minLength(8)
                    ->helperText(fn (string $operation) => $operation === 'edit' ? __('admin.staff.password_hint') : null),
                Select::make('locale')->label(__('admin.fields.language'))->options(SetLocale::LOCALES)->default('en')->native(false),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('name')
            ->columns([
                TextColumn::make('name')->label(__('admin.fields.name'))->searchable()->sortable()->weight('semibold')
                    ->description(fn (User $u) => $u->email),
                TextColumn::make('role')->label(__('admin.fields.role'))->badge()
                    ->formatStateUsing(fn ($state) => __('admin.roles.'.$state))
                    ->color(fn ($state) => Ui::ROLE_COLORS[$state] ?? 'gray')->sortable(),
                TextColumn::make('phone')->label(__('admin.fields.phone'))->placeholder('—'),
                TextColumn::make('created_at')->label(__('admin.fields.created_at'))->date('d M Y')->sortable(),
            ])
            ->filters([SelectFilter::make('role')->label(__('admin.fields.role'))->options(Ui::options('roles', self::STAFF_ROLES))])
            ->recordActions([EditAction::make(), DeleteAction::make()]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageStaff::route('/'),
        ];
    }
}
