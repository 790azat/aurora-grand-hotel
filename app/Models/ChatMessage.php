<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChatMessage extends Model
{
    public const SENDERS = ['guest', 'staff', 'bot'];

    public const MAX_LENGTH = 1000;

    protected $guarded = [];

    protected function casts(): array
    {
        return ['read_at' => 'datetime'];
    }

    protected static function booted(): void
    {
        static::created(function (self $m) {
            $m->conversation()->update(['last_message_at' => $m->created_at, 'updated_at' => now()]);
        });
    }

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(ChatConversation::class, 'chat_conversation_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeUnreadForStaff(Builder $query): Builder
    {
        return $query->where('sender', 'guest')->whereNull('read_at');
    }

    public function scopeUnreadForGuest(Builder $query): Builder
    {
        return $query->whereIn('sender', ['staff', 'bot'])->whereNull('read_at');
    }

    /**
     * Escaped body with line breaks. Links are only rendered for staff/bot messages
     * (guest text is never linkified): absolute http(s) URLs and site paths like "/booking".
     */
    public function getHtmlAttribute(): string
    {
        return $this->toHtml();
    }

    /** @param  bool  $spa  open site links with wire:navigate (public site) instead of a new tab (admin) */
    public function toHtml(bool $spa = true): string
    {
        $html = e($this->body);

        if ($this->sender !== 'guest') {
            $html = preg_replace_callback(
                '~(?<=^|[\s(])(https?://[^\s<>"\']+|/[a-z0-9][a-z0-9\-/]*)~iu',
                function ($m) use ($spa) {
                    $url = rtrim($m[1], '.,;:!?)');
                    $tail = substr($m[1], strlen($url));
                    $internal = str_starts_with($url, '/');
                    $href = $internal ? url($url) : $url;
                    $label = preg_replace('~^https?://(www\.)?~', '', $href);

                    return '<a href="'.$href.'"'.($internal && $spa ? ' wire:navigate' : ' target="_blank" rel="noopener nofollow"').'>'.$label.'</a>'.$tail;
                },
                $html
            );
        }

        return nl2br($html, false);
    }
}
