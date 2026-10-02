<?php

namespace App\Filament\Pages;

use App\Filament\NavGroup;
use App\Filament\Resources\Bookings\BookingResource;
use App\Filament\Resources\Guests\GuestResource;
use App\Models\Booking;
use App\Models\ChatConversation;
use App\Models\ChatMessage;
use App\Models\User;
use App\Services\ChatBot;
use BackedEnum;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Enums\Width;
use Filament\Support\Icons\Heroicon;
use Livewire\Attributes\Url;
use UnitEnum;

/** Two-pane inbox for website live-chat conversations. */
class LiveChat extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChatBubbleLeftRight;

    protected static string|BackedEnum|null $activeNavigationIcon = Heroicon::ChatBubbleLeftRight;

    protected static string|UnitEnum|null $navigationGroup = NavGroup::Inbox;

    protected static ?int $navigationSort = 0;

    protected static ?string $slug = 'live-chat';

    protected string $view = 'filament.pages.live-chat';

    protected Width|string|null $maxContentWidth = Width::Full;

    public const FILTERS = ['open', 'unread', 'closed', 'all'];

    #[Url(as: 'c')]
    public ?int $selected = null;

    #[Url(as: 'filter')]
    public string $filter = 'open';

    public string $search = '';

    public string $reply = '';

    public static function getNavigationLabel(): string
    {
        return __('chat.admin.title');
    }

    public static function getNavigationBadge(): ?string
    {
        $count = ChatConversation::unreadForStaffCount();

        return $count ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'danger';
    }

    public static function getNavigationBadgeTooltip(): ?string
    {
        return __('chat.admin.badge_tooltip');
    }

    public function getTitle(): string
    {
        return __('chat.admin.title');
    }

    public function getSubheading(): ?string
    {
        return __('chat.admin.subheading');
    }

    public function mount(): void
    {
        if (! in_array($this->filter, self::FILTERS, true)) {
            $this->filter = 'open';
        }
        if ($this->selected) {
            $this->markRead();
        }
    }

    public function updatedFilter(): void
    {
        if (! in_array($this->filter, self::FILTERS, true)) {
            $this->filter = 'open';
        }
    }

    public function select(int $id): void
    {
        $this->selected = ChatConversation::whereKey($id)->exists() ? $id : null;
        $this->reply = '';
        $this->resetErrorBag();
        $this->markRead();
    }

    public function deselect(): void
    {
        $this->selected = null;
    }

    /** Called by wire:poll. */
    public function poll(): void
    {
        $this->markRead();
    }

    public function send(): void
    {
        $conversation = $this->current();
        if (! $conversation) {
            return;
        }

        $this->validate(
            ['reply' => 'required|string|max:'.ChatMessage::MAX_LENGTH],
            [],
            ['reply' => __('chat.admin.reply_placeholder')],
        );

        $conversation->messages()->create([
            'sender' => 'staff',
            'user_id' => auth()->id(),
            'body' => trim($this->reply),
        ]);
        $conversation->update(['status' => 'open']);
        $this->reply = '';
        $this->markRead();
    }

    public function toggleStatus(): void
    {
        $conversation = $this->current();
        if (! $conversation) {
            return;
        }
        $closing = $conversation->isOpen();
        $conversation->update(['status' => $closing ? 'closed' : 'open']);

        Notification::make()
            ->title(__($closing ? 'chat.admin.closed_toast' : 'chat.admin.reopened_toast'))
            ->success()
            ->send();
    }

    protected function current(): ?ChatConversation
    {
        return $this->selected ? ChatConversation::find($this->selected) : null;
    }

    protected function markRead(): void
    {
        if ($this->selected) {
            ChatMessage::where('chat_conversation_id', $this->selected)->unreadForStaff()->update(['read_at' => now()]);
        }
    }

    protected function getViewData(): array
    {
        $term = trim($this->search);

        $conversations = ChatConversation::query()
            ->with('latestMessage')
            ->withCount(['messages as unread_count' => fn ($q) => $q->unreadForStaff()])
            ->when($this->filter === 'open', fn ($q) => $q->where('status', 'open'))
            ->when($this->filter === 'closed', fn ($q) => $q->where('status', 'closed'))
            ->when($this->filter === 'unread', fn ($q) => $q->unreadForStaff())
            ->when($term !== '', fn ($q) => $q->where(fn ($q) => $q
                ->where('name', 'like', "%{$term}%")
                ->orWhere('email', 'like', "%{$term}%")
                ->orWhereHas('messages', fn ($m) => $m->where('body', 'like', "%{$term}%"))))
            ->orderByDesc('last_message_at')
            ->limit(100)
            ->get();

        $current = $this->current()?->load(['messages.user:id,name']);

        $bookings = collect();
        $guestUrl = null;
        if ($current?->email) {
            $bookings = Booking::where('email', $current->email)->with('roomType')->latest('check_in')->limit(4)->get();
            $guest = User::where('email', $current->email)->where('role', 'guest')->first();
            $guestUrl = $guest ? GuestResource::getUrl('view', ['record' => $guest]) : null;
        }

        return [
            'conversations' => $conversations,
            'current' => $current,
            'bookings' => $bookings->map(fn (Booking $b) => [
                'ref' => $b->reference,
                'dates' => $b->check_in->locale(app()->getLocale())->isoFormat('D MMM').' → '.$b->check_out->locale(app()->getLocale())->isoFormat('D MMM YYYY'),
                'room' => $b->roomType?->name,
                'status' => $b->status,
                'url' => BookingResource::getUrl('view', ['record' => $b]),
            ]),
            'guestUrl' => $guestUrl,
            'counts' => [
                'open' => ChatConversation::where('status', 'open')->count(),
                'unread' => ChatConversation::unreadForStaffCount(),
            ],
            'quietMinutes' => ChatBot::STAFF_QUIET_MINUTES,
        ];
    }
}
