<?php

namespace App\Models;

use App\Services\PlayerAvatarService;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name', 'email', 'password', 'mc_username', 'mc_uuid',
        'mc_verified', 'avatar', 'bio', 'is_admin',
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];

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
            // 本地自定义头像加时间戳，上传后立即看到更新
            if (str_starts_with($this->avatar, '/avatars/')) {
                return $this->avatar . '?v=' . ($this->updated_at?->timestamp ?? time());
            }
            return $this->avatar;
        }

        // 未上传自定义头像，无论是否绑定 MC 账号，都用名字首字母
        return PlayerAvatarService::initialAvatar($this->name ?: 'U');
    }
}
