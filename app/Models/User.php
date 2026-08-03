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
        'mc_verified', 'avatar', 'bio', 'chat_bg', 'is_admin',
        'is_blocked', 'blocked_at', 'block_reason',
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];

    protected $appends = ['avatar_url'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'mc_verified' => 'boolean',
            'is_admin' => 'boolean',
            'is_blocked' => 'boolean',
            'blocked_at' => 'datetime',
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

    /**
     * 是否被封禁
     */
    public function isBlocked(): bool
    {
        return (bool) $this->is_blocked;
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

    /**
     * Laravel accessor: makes avatar_url available in JSON serialization
     */
    public function getAvatarUrlAttribute(): string
    {
        return $this->getAvatarUrl();
    }

    /**
     * 获取聊天背景图 URL，若无自定义则返回空（前端用默认背景）
     */
    /**
     * 获取聊天背景图 URL（带缓存时间戳），若无自定义则返回空字符串。
     * 使用 chat_bg 字段的修改时间戳而非用户整体 updated_at，避免
     * 用户修改其他资料时背景图缓存也被错误刷新。
     */
    public function getChatBgUrl(): string
    {
        if (empty($this->chat_bg)) return '';
        if (!str_starts_with($this->chat_bg, '/chat-bgs/')) return '';

        // 从完整路径提取文件名，通过文件修改时间生成缓存戳
        $path = public_path(ltrim($this->chat_bg, '/'));
        $mtime = is_file($path) ? filemtime($path) : time();
        return $this->chat_bg . '?v=' . $mtime;
    }
}
