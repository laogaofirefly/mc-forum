<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;

#[Fillable([
    'name', 'email', 'password', 'mc_username', 'mc_uuid',
    'mc_verified', 'avatar', 'bio', 'is_admin',
])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'mc_verified' => 'boolean',
            'is_admin' => 'boolean',
        ];
    }

    public function setPasswordAttribute(string $value): void
    {
        $this->attributes['password'] = Hash::make($value);
    }

    public function threads(): HasMany
    {
        return $this->hasMany(Thread::class);
    }

    public function replies(): HasMany
    {
        return $this->hasMany(Reply::class);
    }

    public function likes(): HasMany
    {
        return $this->hasMany(Like::class);
    }

    public function isAdmin(): bool
    {
        return $this->is_admin;
    }

    public function getAvatarUrl(): string
    {
        if ($this->avatar) {
            return $this->avatar;
        }

        if ($this->mc_uuid) {
            return 'https://crafatar.com/avatars/' . $this->mc_uuid . '?size=100&default=MHF_Steve';
        }

        return 'https://crafatar.com/avatars/MHF_Steve?size=100';
    }
}
