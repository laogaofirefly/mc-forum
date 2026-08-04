<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function show(User $user): View
    {
        $threads = $user->threads()->with('category')->latest()->take(5)->get();

        return view('profile.show', compact('user', 'threads'));
    }

    public function edit(): View
    {
        $user = Auth::user();
        return view('profile.edit', compact('user'));
    }

    public function update(Request $request): RedirectResponse
    {
        $user = Auth::user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:users,name,' . $user->id],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'bio' => ['nullable', 'string', 'max:500'],
        ]);

        $user->update($validated);

        return redirect()->route('profile.show', $user)->with('success', '个人资料已更新。');
    }

    public function avatar(Request $request): RedirectResponse
    {
        $request->validate([
            'avatar' => ['required', 'image', 'mimes:jpg,jpeg,png,webp,gif', 'max:2048'],
        ], [
            'avatar.required' => '请选择要上传的图片',
            'avatar.image'    => '上传的文件必须是图片',
            'avatar.mimes'    => '仅支持 JPG/PNG/WEBP/GIF 格式',
            'avatar.max'      => '图片大小不能超过 2MB',
        ]);

        $user = Auth::user();
        $file = $request->file('avatar');
        
        // 裁剪参数
        $cropX = (float) $request->input('crop_x', 0);
        $cropY = (float) $request->input('crop_y', 0);
        $cropScale = (float) $request->input('crop_scale', 1);
        $cropSize = (int) $request->input('crop_size', 300);

        // 删除旧头像
        if ($user->avatar && str_starts_with($user->avatar, '/avatars/')) {
            $oldPath = public_path(ltrim($user->avatar, '/'));
            if (is_file($oldPath)) @unlink($oldPath);
        }

        $ext  = $file->getClientOriginalExtension();
        $name = 'avatar_' . $user->id . '_' . time() . '.' . $ext;
        $dir = public_path('avatars');
        if (!is_dir($dir)) mkdir($dir, 0755, true);

        if ($cropX !== 0.0 || $cropY !== 0.0 || $cropScale !== 1.0) {
            // 有裁剪参数，进行裁剪
            $this->cropAvatar($file, $dir . '/' . $name, $cropX, $cropY, $cropScale, $cropSize);
        } else {
            // 无裁剪参数，直接保存
            $file->move($dir, $name);
        }

        $user->avatar = '/avatars/' . $name;
        $user->save();

        return redirect()->route('profile.show', $user)->with('success', '头像更新成功！');
    }

    /**
     * 裁剪头像图片
     */
    private function cropAvatar($file, string $destPath, float $cropX, float $cropY, float $cropScale, int $cropSize): void
    {
        $srcPath = $file->getRealPath();
        $ext = strtolower($file->getClientOriginalExtension());
        
        // 创建源图像
        switch ($ext) {
            case 'jpg':
            case 'jpeg':
                $src = imagecreatefromjpeg($srcPath);
                break;
            case 'png':
                $src = imagecreatefrompng($srcPath);
                break;
            case 'webp':
                $src = imagecreatefromwebp($srcPath);
                break;
            case 'gif':
                $src = imagecreatefromgif($srcPath);
                break;
            default:
                $file->move(dirname($destPath), basename($destPath));
                return;
        }

        if (!$src) {
            $file->move(dirname($destPath), basename($destPath));
            return;
        }

        $srcW = imagesx($src);
        $srcH = imagesy($src);

        // 裁剪区域：cropX/cropY 是相对于原图的偏移比例（0~1），cropScale 是缩放比例
        // 裁剪框大小 = min(srcW, srcH) / cropScale
        $cropBoxSize = min($srcW, $srcH) / $cropScale;
        $srcCropX = (int) round($cropX * $srcW);
        $srcCropY = (int) round($cropY * $srcH);

        // 限制裁剪区域不超出原图
        $srcCropX = max(0, min($srcCropX, $srcW - $cropBoxSize));
        $srcCropY = max(0, min($srcCropY, $srcH - $cropBoxSize));

        // 创建目标图像（正方形）
        $dest = imagecreatetruecolor($cropSize, $cropSize);

        // 处理 PNG 透明背景
        if ($ext === 'png') {
            imagealphablending($dest, false);
            imagesavealpha($dest, true);
            $transparent = imagecolorallocatealpha($dest, 0, 0, 0, 127);
            imagefill($dest, 0, 0, $transparent);
        }

        imagecopyresampled(
            $dest, $src,
            0, 0,                    // 目标 x, y
            $srcCropX, $srcCropY,    // 源 x, y
            $cropSize, $cropSize,    // 目标宽高
            (int) $cropBoxSize, (int) $cropBoxSize  // 源宽高
        );

        // 保存
        switch ($ext) {
            case 'jpg':
            case 'jpeg':
                imagejpeg($dest, $destPath, 90);
                break;
            case 'png':
                imagepng($dest, $destPath, 8);
                break;
            case 'webp':
                imagewebp($dest, $destPath, 90);
                break;
            case 'gif':
                imagegif($dest, $destPath);
                break;
        }

        imagedestroy($src);
        imagedestroy($dest);
    }


/**
     * 删除用户本地背景图文件（公共逻辑）
     */
    private function removeChatBgFile($chatBgPath): void
    {
        if ($chatBgPath && str_starts_with($chatBgPath, '/chat-bgs/')) {
            $fullPath = public_path(ltrim($chatBgPath, '/'));
            if (is_file($fullPath)) @unlink($fullPath);
        }
    }

    /**
     * 上传自定义聊天背景图
     */
    public function chatBg(Request $request): RedirectResponse
    {
        $request->validate([
            'chat_bg' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ], [
            'chat_bg.required' => '请选择要上传的图片',
            'chat_bg.image'    => '上传的文件必须是图片',
            'chat_bg.mimes'    => '仅支持 JPG/PNG/WEBP 格式',
            'chat_bg.max'      => '图片大小不能超过 5MB',
        ]);

        $user = Auth::user();
        $this->removeChatBgFile($user->chat_bg);

        $file = $request->file('chat_bg');
        $name = sprintf('chat_bg_%d_%d.%s', $user->id, time(), $file->getClientOriginalExtension());

        $dir = public_path('chat-bgs');
        if (!is_dir($dir)) mkdir($dir, 0755, true);
        $file->move($dir, $name);

        $user->chat_bg = '/chat-bgs/' . $name;
        $user->save();

        return redirect()->route('profile.edit')->with('success', '聊天背景图已更新！');
    }

    /**
     * 移除自定义聊天背景图
     */
    public function chatBgRemove(): RedirectResponse
    {
        $user = Auth::user();
        $this->removeChatBgFile($user->chat_bg);
        $user->chat_bg = null;
        $user->save();

        return redirect()->route('profile.edit')->with('success', '聊天背景图已移除。');
    }

    public function mcBind(): View
    {
        $user = Auth::user();
        return view('profile.mc-bind', compact('user'));
    }

    public function mcBindUpdate(Request $request): RedirectResponse
    {
        $user = Auth::user();

        $validated = $request->validate([
            'mc_username' => ['required', 'string', 'max:16'],
        ]);

        $mcUsername = $validated['mc_username'];
        $uuid = $this->getMinecraftUuid($mcUsername);

        if (! $uuid) {
            return back()->withErrors(['mc_username' => '无法验证该 MC 用户名，请检查后重试。'])->withInput();
        }

        // 限制同一个 MC 账号不能绑定两个网站账号
        // 通过用户名（大小写不敏感）或 UUID 判断是否已被其他账号绑定
        $duplicate = User::where('id', '!=', $user->id)
            ->where(function ($q) use ($mcUsername, $uuid) {
                $q->whereRaw('LOWER(mc_username) = ?', [strtolower($mcUsername)])
                  ->orWhere('mc_uuid', $uuid);
            })
            ->exists();

        if ($duplicate) {
            return back()->withErrors(['mc_username' => '该 MC 账号已被其他网站账号绑定，如需解绑请联系管理员。'])->withInput();
        }

        $user->mc_username = $mcUsername;
        $user->mc_uuid = $uuid;
        $user->mc_verified = true;
        $user->save();

        return redirect()->route('profile.show', $user)->with('success', 'MC 账号绑定成功！');
    }

    private function getMinecraftUuid(string $username): ?string
    {
        $url = 'https://api.mojang.com/users/profiles/minecraft/' . urlencode($username);

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode === 200 && $response) {
            $data = json_decode($response, true);
            if (isset($data['id'])) {
                $uuid = $data['id'];
                return substr($uuid, 0, 8) . '-' . substr($uuid, 8, 4) . '-' . substr($uuid, 12, 4) . '-' . substr($uuid, 16, 4) . '-' . substr($uuid, 20);
            }
        }

        return null;
    }
}
