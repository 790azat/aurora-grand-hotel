<?php

namespace App\Filament\Resources\ContactMessages;

use App\Filament\NavGroup;
use App\Filament\Resources\ContactMessages\Pages\ListContactMessages;
use App\Filament\Resources\ContactMessages\Tables\ContactMessagesTable;
use App\Models\ContactMessage;
use BackedEnum;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class ContactMessageResource extends Resource
{
    protected static ?string $model = ContactMessage::class;

    protected static bool $hasTitleCaseModelLabel = false;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedInboxArrowDown;

    protected static string|UnitEnum|null $navigationGroup = NavGroup::Inbox;

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'subject';

    protected static bool $isGloballySearchable = false;

    public static function getModelLabel(): string
    {
        return __('admin.models.contact_message.singular');
    }

    public static function getPluralModelLabel(): string
    {
        return __('admin.models.contact_message.plural');
    }

    public static function getNavigationBadge(): ?string
    {
        $count = ContactMessage::where('is_read', false)->count();

        return $count ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'danger';
    }

    public static function getNavigationBadgeTooltip(): ?string
    {
        return __('admin.inbox.unread_badge');
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema->columns(2)->components([
            TextEntry::make('name')->label(__('admin.fields.name'))->weight('semibold'),
            TextEntry::make('created_at')->label(__('admin.fields.date'))->dateTime('d M Y, H:i'),
            TextEntry::make('email')->label(__('admin.fields.email'))->copyable()->url(fn ($record) => 'mailto:'.$record->email)->color('primary'),
            TextEntry::make('phone')->label(__('admin.fields.phone'))->placeholder('—'),
            TextEntry::make('subject')->label(__('admin.fields.subject'))->placeholder('—')->columnSpanFull(),
            TextEntry::make('message')->label(__('admin.fields.message'))->columnSpanFull()->prose(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return ContactMessagesTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListContactMessages::route('/'),
        ];
    }
}
