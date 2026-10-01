<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password', 'role', 'phone', 'country', 'locale'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /** Staff roles with admin panel access; `guest` is a website customer. */
    public const ROLES = ['admin', 'manager', 'reception', 'guest'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return $this->isStaff();
    }

    public function isStaff(): bool
    {
        return in_array($this->role, ['admin', 'manager', 'reception'], true);
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /** Admins and managers can manage content, pricing and inventory. */
    public function isManager(): bool
    {
        return in_array($this->role, ['admin', 'manager'], true);
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class)->latest('check_in');
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }
}
