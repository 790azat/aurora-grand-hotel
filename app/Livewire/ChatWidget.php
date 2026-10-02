<?php

namespace App\Livewire;

use App\Models\ChatConversation;
use App\Models\ChatMessage;
use App\Services\ChatBot;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Attributes\Locked;
use Livewire\Component;

/** Floating live-chat bubble on every public page. */
class ChatWidget extends Component
{
    public const COOKIE = 'aurora_chat';

    /** Messages per minute a visitor may send. */
    public const RATE_LIMIT = 8;

    #[Locked]
    public ?string $token = null;

    public bool $open = false;

    public string $body = '';

    public string $name = '';

    public string $email = '';

    /** Bot message id just created, rendered with a short "typing" reveal. */
    public ?int $typingId = null;

    public function mount(): void
    {
        $token = session('chat_token') ?? request()->cookie(self::COOKIE);

        if (is_string($token) && ChatConversation::where('token', $token)->exists()) {
            $this->token = $token;
            session(['chat_token' => $token]);
        }

        if ($user = auth()->user()) {
            $this->name = (string) $user->name;
            $this->email = (string) $user->email;
        }
    }

    protected function conversation(): ?ChatConversation
    {
        return $this->token ? ChatConversation::firstWhere('token', $this->token) : null;
    }

    /** Panel opened/closed on the client ($wire.open). */
    public function updatedOpen(): void
    {
        $this->typingId = null;
        if ($this->open) {
            $this->markRead();
        }
    }

    public function poll(): void
    {
        $this->typingId = null;
        if ($this->open) {
            $this->markRead();
        }
    }

    public function ask(string $topic): void
    {
        if (! array_key_exists($topic, trans('chat.quick_text'))) {
            return;
        }
        $this->body = __('chat.quick_text.'.$topic);
        $this->send();
    }

    public function send(): void
    {
        $this->resetErrorBag();
        $text = trim(str_replace("\r\n", "\n", $this->body));
        $text = preg_replace("/\n{3,}/", "\n\n", $text);

        if ($text === '') {
            $this->addError('body', __('chat.widget.empty'));

            return;
        }
        if (mb_strlen($text) > ChatMessage::MAX_LENGTH) {
            $this->addError('body', __('chat.widget.too_long', ['max' => ChatMessage::MAX_LENGTH]));

            return;
        }

        $key = 'chat:'.(session()->getId() ?: request()->ip());
        if (RateLimiter::tooManyAttempts($key, self::RATE_LIMIT)) {
            $this->addError('body', __('chat.widget.too_many', ['seconds' => RateLimiter::availableIn($key)]));

            return;
        }
        RateLimiter::hit($key, 60);

        $conversation = $this->conversation() ?? $this->start();

        $name = trim(mb_substr($this->name, 0, 80));
        $email = filter_var(trim($this->email), FILTER_VALIDATE_EMAIL) ? mb_strtolower(trim($this->email)) : null;
        $conversation->fill(array_filter([
            'name' => $conversation->name ?: ($name ?: null),
            'email' => $conversation->email ?: $email,
            'status' => 'open',
            'locale' => app()->getLocale(),
        ]))->save();

        $conversation->messages()->create(['sender' => 'guest', 'body' => $text]);
        $this->body = '';

        $reply = app(ChatBot::class)->respond($conversation, $text);
        $this->typingId = $reply?->id;
        $this->markRead();
    }

    protected function start(): ChatConversation
    {
        $conversation = ChatConversation::create([
            'locale' => app()->getLocale(),
            'user_id' => auth()->id(),
        ]);

        $this->token = $conversation->token;
        session(['chat_token' => $conversation->token]);
        Cookie::queue(self::COOKIE, $conversation->token, 60 * 24 * 30);

        return $conversation;
    }

    protected function markRead(): void
    {
        if ($c = $this->conversation()) {
            $c->messages()->unreadForGuest()->update(['read_at' => now()]);
        }
    }

    public function render()
    {
        $conversation = $this->conversation();
        $messages = $conversation?->messages()->with('user:id,name')->reorder()->latest('id')->limit(100)->get()->reverse()->values() ?? collect();

        return view('livewire.chat-widget', [
            'conversation' => $conversation,
            'messages' => $messages,
            'unread' => $conversation && ! $this->open ? $conversation->messages()->unreadForGuest()->count() : 0,
        ]);
    }
}
