<?php

namespace App\Filament\Resources\ContactMessages\Pages;

use App\Filament\Resources\ContactMessages\ContactMessageResource;
use App\Models\ContactMessage;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;

class ListContactMessages extends ListRecords
{
    protected static string $resource = ContactMessageResource::class;

    public function getTabs(): array
    {
        return [
            'all' => Tab::make(__('admin.tabs.all')),
            'unread' => Tab::make(__('admin.inbox.unread'))
                ->badge(ContactMessage::where('is_read', false)->count() ?: null)
                ->badgeColor('danger')
                ->modifyQueryUsing(fn ($query) => $query->where('is_read', false)),
        ];
    }
}
