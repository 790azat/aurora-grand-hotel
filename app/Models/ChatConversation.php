<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;

/** A live-chat thread between a website visitor and the front desk. */
class ChatConversation extends Model
{
    public const STATUSES = ['open', 'closed'];

    protected $guarded = [];

    protected function casts(): array
    {
        return ['last_message_at' => 'datetime'];
    }

    protected static function booted(): void
    {
        static::creating(function (self $c) {
            $c->token ??= (string) Str::uuid();
            $c->last_message_at ??= now();
        });
    }

    public function messages(): HasMany
    {
        return $this->hasMany(ChatMessage::class)->orderBy('id');
    }

    public function latestMessage(): HasOne
    {
        return $this->hasOne(ChatMessage::class)->latestOfMany();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** Conversations with guest messages staff hasn't read yet. */
    public function scopeUnreadForStaff(Builder $query): Builder
    {
        return $query->whereHas('messages', fn ($q) => $q->where('sender', 'guest')->whereNull('read_at'));
    }

    public static function unreadForStaffCount(): int
    {
        return static::query()->unreadForStaff()->count();
    }

    public function getDisplayNameAttribute(): string
    {
        return $this->name ?: __('chat.admin.guest_n', ['id' => $this->id]);
    }

    public function isOpen(): bool
    {
        return $this->status === 'open';
    }
}
