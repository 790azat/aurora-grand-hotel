<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;

class Booking extends Model
{
    public const STATUSES = ['pending', 'confirmed', 'checked_in', 'checked_out', 'cancelled', 'no_show'];

    public const PAYMENT_STATUSES = ['unpaid', 'paid', 'refunded'];

    public const SOURCES = ['website', 'admin', 'phone', 'booking_com', 'expedia', 'walk_in'];

    /** Statuses that occupy a room. */
    public const ACTIVE_STATUSES = ['pending', 'confirmed', 'checked_in', 'checked_out'];

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'check_in' => 'date',
            'check_out' => 'date',
            'room_total' => 'decimal:2',
            'extras_total' => 'decimal:2',
            'discount' => 'decimal:2',
            'tax' => 'decimal:2',
            'total' => 'decimal:2',
            'amount_paid' => 'decimal:2',
            'confirmed_at' => 'datetime',
            'cancelled_at' => 'datetime',
            'checked_in_at' => 'datetime',
            'checked_out_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Booking $booking) {
            $booking->reference ??= static::generateReference();
            $booking->nights = $booking->check_in->diffInDays($booking->check_out);
        });
    }

    public static function generateReference(): string
    {
        do {
            $ref = 'AGH-'.strtoupper(Str::random(6));
        } while (static::where('reference', $ref)->exists());

        return $ref;
    }

    public function getRouteKeyName(): string
    {
        return 'reference';
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function roomType(): BelongsTo
    {
        return $this->belongsTo(RoomType::class);
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    public function promoCode(): BelongsTo
    {
        return $this->belongsTo(PromoCode::class);
    }

    public function extras(): BelongsToMany
    {
        return $this->belongsToMany(Extra::class)->withPivot(['quantity', 'unit_price', 'total']);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function review(): HasOne
    {
        return $this->hasOne(Review::class);
    }

    /** Bookings that overlap the [checkIn, checkOut) range and occupy a room. */
    public function scopeOverlapping(Builder $query, $checkIn, $checkOut): Builder
    {
        return $query->whereIn('status', self::ACTIVE_STATUSES)
            ->whereDate('check_in', '<', $checkOut)
            ->whereDate('check_out', '>', $checkIn);
    }

    public function getGuestNameAttribute(): string
    {
        return trim($this->first_name.' '.$this->last_name);
    }

    public function getBalanceAttribute(): float
    {
        return max(0, (float) $this->total - (float) $this->amount_paid);
    }

    /** Free cancellation until N hours before check-in (see settings). */
    public function canBeCancelledByGuest(): bool
    {
        if (! in_array($this->status, ['pending', 'confirmed'], true)) {
            return false;
        }

        $hours = (int) Setting::get('free_cancellation_hours', 48);

        return now()->addHours($hours)->lt($this->check_in->copy()->setTime(14, 0));
    }

    public function canBeReviewed(): bool
    {
        return $this->status === 'checked_out' && ! $this->review()->exists();
    }
}
