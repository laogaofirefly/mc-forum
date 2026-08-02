<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class ImageUploadController extends Controller
{
    /** 允许的图片扩展名 */
    private const ALLOWED_MIMES = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];

    /** 单文件最大 5MB */
    private const MAX_SIZE = 5 * 1024 * 1024;

    /**
     * 上传图片（发帖/回复共用）
     * 返回 markdown 格式的图片链接，前端直接插入编辑器
     */
    public function upload(Request $request): JsonResponse
    {
        if (! Auth::check()) {
            return response()->json(['ok' => false, 'message' => '请先登录'], 401);
        }

        $request->validate([
            'file' => ['required', 'file'],
        ]);

        $file = $request->file('file');
        if (! $file || ! $file->isValid()) {
            return response()->json(['ok' => false, 'message' => '上传失败，请重试'], 400);
        }

        // 校验类型
        $mime = $file->getMimeType();
        if (! in_array($mime, self::ALLOWED_MIMES, true)) {
            return response()->json(['ok' => false, 'message' => '仅支持 JPG / PNG / WEBP / GIF 格式'], 422);
        }

        // 校验大小
        if ($file->getSize() > self::MAX_SIZE) {
            return response()->json(['ok' => false, 'message' => '图片大小不能超过 5MB'], 422);
        }

        // 存储到 public/uploads/threads，按年月分目录
        $dir = 'uploads/threads/' . date('Ym');
        $name = 'img_' . Auth::id() . '_' . time() . '_' . Str::random(6) . '.' . $file->getClientOriginalExtension();

        try {
            $file->move(public_path($dir), $name);
        } catch (\Throwable $e) {
            return response()->json(['ok' => false, 'message' => '保存失败：' . $e->getMessage()], 500);
        }

        $url = '/' . $dir . '/' . $name;

        return response()->json([
            'ok' => true,
            'url' => $url,
            'markdown' => '![' . ($file->getClientOriginalName() ?: '图片') . '](' . $url . ')',
        ]);
    }
}
