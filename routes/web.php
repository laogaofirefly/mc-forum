<?php

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\GameChatController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReplyController;
use App\Http\Controllers\ServerStatusController;
use App\Http\Controllers\ThreadController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::get('/register', [RegisterController::class, 'create'])->name('register');
Route::post('/register', [RegisterController::class, 'store']);

Route::get('/login', [LoginController::class, 'create'])->name('login');
Route::post('/login', [LoginController::class, 'store']);
Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');

Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit')->middleware('auth');
Route::post('/profile/edit', [ProfileController::class, 'update'])->name('profile.update')->middleware('auth');
Route::get('/profile/mc-bind', [ProfileController::class, 'mcBind'])->name('profile.mc-bind')->middleware('auth');
Route::post('/profile/mc-bind', [ProfileController::class, 'mcBindUpdate'])->name('profile.mc-bind.update')->middleware('auth');
Route::get('/profile/{user}', [ProfileController::class, 'show'])->name('profile.show');

Route::get('/categories', [CategoryController::class, 'index'])->name('categories.index');
Route::get('/categories/{category:slug}', [CategoryController::class, 'show'])->name('categories.show');

Route::get('/threads', [ThreadController::class, 'index'])->name('threads.index');
Route::get('/threads/create', [ThreadController::class, 'create'])->name('threads.create')->middleware('auth');
Route::post('/threads', [ThreadController::class, 'store'])->name('threads.store')->middleware('auth');
Route::get('/threads/{thread:slug}', [ThreadController::class, 'show'])->name('threads.show');
Route::get('/threads/{thread:slug}/edit', [ThreadController::class, 'edit'])->name('threads.edit')->middleware('auth');
Route::put('/threads/{thread:slug}', [ThreadController::class, 'update'])->name('threads.update')->middleware('auth');
Route::delete('/threads/{thread:slug}', [ThreadController::class, 'destroy'])->name('threads.destroy')->middleware('auth');

Route::post('/threads/{thread:slug}/replies', [ReplyController::class, 'store'])->name('replies.store')->middleware('auth');
Route::get('/replies/{reply}/edit', [ReplyController::class, 'edit'])->name('replies.edit')->middleware('auth');
Route::put('/replies/{reply}', [ReplyController::class, 'update'])->name('replies.update')->middleware('auth');
Route::delete('/replies/{reply}', [ReplyController::class, 'destroy'])->name('replies.destroy')->middleware('auth');

Route::get('/game-chat', [GameChatController::class, 'index'])->name('game-chat');
Route::get('/game-chat/fetch', [GameChatController::class, 'fetch'])->name('game-chat.fetch');
Route::post('/game-chat/demo', [GameChatController::class, 'demo'])->name('game-chat.demo')->middleware('auth');

Route::get('/admin/monitor', [\App\Http\Controllers\Admin\ServerMonitorController::class, 'index'])->name('admin.monitor')->middleware('auth');
Route::get('/admin/monitor/metrics', [\App\Http\Controllers\Admin\ServerMonitorController::class, 'metrics'])->name('admin.monitor.metrics')->middleware('auth');

Route::get('/api/server-status', [ServerStatusController::class, 'index'])->name('server-status');

// 临时：聊天功能诊断页（访问 /chat-test 即可查看详细测试结果）
Route::get('/chat-test', function () {
    $steps = [];
    $fatal = false;

    // 1. 环境
    $steps[] = [
        'ok' => true,
        'title' => '运行环境',
        'detail' => 'PHP ' . PHP_VERSION . ' / Laravel ' . app()->version(),
    ];

    // 2. 数据库连接
    try {
        $db = DB::connection();
        $driver = $db->getDriverName();
        $dbName = $db->getDatabaseName();
        $db->getPdo(); // 试一下连接
        $steps[] = [
            'ok' => true,
            'title' => '数据库连接',
            'detail' => "{$driver} / {$dbName}",
        ];
    } catch (Throwable $e) {
        $steps[] = [
            'ok' => false,
            'title' => '数据库连接',
            'detail' => '失败：' . $e->getMessage(),
        ];
        $fatal = true;
    }

    // 3. 表是否存在
    try {
        $exists = Schema::hasTable('game_chat_messages');
        if ($exists) {
            $count = DB::table('game_chat_messages')->count();
            $steps[] = [
                'ok' => true,
                'title' => '表 game_chat_messages',
                'detail' => "存在，当前有 {$count} 条记录",
            ];
        } else {
            $steps[] = [
                'ok' => false,
                'title' => '表 game_chat_messages',
                'detail' => '不存在！请先运行 php artisan migrate --force',
            ];
            $fatal = true;
        }
    } catch (Throwable $e) {
        $steps[] = [
            'ok' => false,
            'title' => '表 game_chat_messages',
            'detail' => '检查失败：' . $e->getMessage(),
        ];
        $fatal = true;
    }

    // 4. 模型 timestamps 设置
    try {
        $ref = new ReflectionClass(\App\Models\GameChatMessage::class);
        $prop = $ref->getProperty('timestamps');
        $prop->setAccessible(true);
        $val = $prop->getValue(new \App\Models\GameChatMessage());
        if ($val === false) {
            $steps[] = [
                'ok' => true,
                'title' => '模型 timestamps 设置',
                'detail' => '已关闭（public $timestamps = false）',
            ];
        } else {
            $steps[] = [
                'ok' => false,
                'title' => '模型 timestamps 设置',
                'detail' => '未关闭！当前值 = ' . var_export($val, true) . '，会导致插入时报 updated_at 列不存在的错误',
            ];
        }
    } catch (Throwable $e) {
        $steps[] = [
            'ok' => false,
            'title' => '模型 timestamps 设置',
            'detail' => '检查失败：' . $e->getMessage(),
        ];
    }

    // 5. 实际插入一条测试数据
    $insertedId = null;
    if (!$fatal) {
        try {
            $msg = '诊断页测试消息 ' . now()->format('Y-m-d H:i:s');
            $m = \App\Models\GameChatMessage::addMessage('Diagnose', $msg);
            $insertedId = $m->id;
            $steps[] = [
                'ok' => true,
                'title' => '插入测试消息',
                'detail' => "成功！新记录 ID = {$m->id}，内容：{$m->message}",
            ];
        } catch (Throwable $e) {
            $steps[] = [
                'ok' => false,
                'title' => '插入测试消息',
                'detail' => '失败：' . $e->getMessage() . "\n" . '位置：' . $e->getFile() . ':' . $e->getLine(),
            ];
        }
    } else {
        $steps[] = [
            'ok' => null,
            'title' => '插入测试消息',
            'detail' => '前面步骤有致命错误，跳过',
        ];
    }

    // 6. 查询刚插入的数据
    if ($insertedId) {
        try {
            $found = DB::table('game_chat_messages')->where('id', $insertedId)->first();
            if ($found) {
                $steps[] = [
                    'ok' => true,
                    'title' => '查询刚插入的记录',
                    'detail' => '查到了：player=' . $found->player_name . ' / message=' . $found->message,
                ];
            } else {
                $steps[] = [
                    'ok' => false,
                    'title' => '查询刚插入的记录',
                    'detail' => '没查到（ID=' . $insertedId . '）',
                ];
            }
        } catch (Throwable $e) {
            $steps[] = [
                'ok' => false,
                'title' => '查询刚插入的记录',
                'detail' => '查询失败：' . $e->getMessage(),
            ];
        }
    }

    // 7. MC 服务器日志路径配置
    $mcPath = config('services.minecraft.log_path', '');
    $logPath = $mcPath ? (rtrim($mcPath, '\\/') . DIRECTORY_SEPARATOR . 'logs' . DIRECTORY_SEPARATOR . 'latest.log') : '';

    if (empty($mcPath)) {
        $steps[] = [
            'ok' => false,
            'title' => 'MC_SERVER_PATH 配置',
            'detail' => "未配置！请在网站根目录的 .env 文件里加一行：\nMC_SERVER_PATH=C:\\Users\\Administrator\\Desktop\\server\n（改成你实际的 MC 服务器根目录）",
        ];
    } else {
        $steps[] = [
            'ok' => true,
            'title' => 'MC_SERVER_PATH 配置',
            'detail' => '已配置：' . $mcPath,
        ];
    }

    // 8. 日志文件是否存在
    if (! empty($logPath)) {
        if (file_exists($logPath)) {
            $size = filesize($logPath);
            $steps[] = [
                'ok' => true,
                'title' => '日志文件 latest.log',
                'detail' => '存在，路径：' . $logPath . "\n大小：" . number_format($size) . ' bytes',
            ];
        } else {
            $steps[] = [
                'ok' => false,
                'title' => '日志文件 latest.log',
                'detail' => '不存在：' . $logPath . "\n请确认 MC 服务器已启动过至少一次，并且路径正确",
            ];
        }
    } else {
        $steps[] = [
            'ok' => null,
            'title' => '日志文件 latest.log',
            'detail' => '前面 MC_SERVER_PATH 未配置，跳过',
        ];
    }

    // 9. 日志文件是否可读
    if (! empty($logPath) && file_exists($logPath)) {
        if (is_readable($logPath)) {
            $steps[] = [
                'ok' => true,
                'title' => '日志文件读取权限',
                'detail' => '可读 ✓',
            ];
        } else {
            $steps[] = [
                'ok' => false,
                'title' => '日志文件读取权限',
                'detail' => '不可读！需要给 Web 用户（IIS/IUSR 或 Apache 服务账户）对该文件的读取权限',
            ];
        }
    } else {
        $steps[] = [
            'ok' => null,
            'title' => '日志文件读取权限',
            'detail' => '前面步骤未通过，跳过',
        ];
    }

    $allOk = collect($steps)->every(fn($s) => $s['ok'] === true || $s['ok'] === null);
    ?>
    <!doctype html>
    <html lang="zh-CN">
    <head>
        <meta charset="utf-8">
        <title>聊天功能诊断</title>
        <style>
            body { font-family: system-ui, -apple-system, "PingFang SC", "Microsoft YaHei", sans-serif; max-width: 780px; margin: 40px auto; padding: 0 20px; background: #0f172a; color: #e2e8f0; }
            h1 { font-size: 22px; margin-bottom: 8px; }
            .sub { color: #94a3b8; font-size: 14px; margin-bottom: 28px; }
            .summary { padding: 12px 16px; border-radius: 8px; margin-bottom: 20px; font-size: 15px; font-weight: 600; }
            .summary.ok { background: rgba(34,197,94,.15); color: #4ade80; border: 1px solid rgba(34,197,94,.3); }
            .summary.bad { background: rgba(239,68,68,.15); color: #f87171; border: 1px solid rgba(239,68,68,.3); }
            .step { padding: 12px 14px; border-radius: 8px; margin-bottom: 10px; border: 1px solid #334155; background: #1e293b; }
            .step.ok { border-color: rgba(34,197,94,.35); }
            .step.bad { border-color: rgba(239,68,68,.35); }
            .step.skip { border-color: #475569; opacity: .7; }
            .row { display: flex; align-items: flex-start; gap: 10px; }
            .icon { flex-shrink: 0; width: 22px; height: 22px; border-radius: 999px; display: inline-flex; align-items: center; justify-content: center; font-weight: 700; font-size: 14px; }
            .icon.ok { background: #22c55e; color: #fff; }
            .icon.bad { background: #ef4444; color: #fff; }
            .icon.skip { background: #64748b; color: #fff; }
            .t { font-size: 15px; font-weight: 600; margin-bottom: 4px; }
            .d { font-size: 13px; color: #cbd5e1; white-space: pre-wrap; word-break: break-all; }
            .tip { margin-top: 24px; padding: 14px; background: #1e293b; border-radius: 8px; font-size: 13px; color: #94a3b8; line-height: 1.7; }
            .tip b { color: #f1f5f9; }
            a { color: #60a5fa; }
        </style>
    </head>
    <body>
        <h1>💬 聊天功能诊断页</h1>
        <div class="sub">自动检查游戏聊天相关的表结构、模型设置、插入查询是否正常</div>

        <div class="summary <?= $allOk ? 'ok' : 'bad' ?>">
            <?= $allOk ? '✅ 所有检查通过！聊天功能应该可用了，刷新 /game-chat 查看效果' : '❌ 发现问题，请按下方步骤提示解决' ?>
        </div>

        <?php foreach ($steps as $i => $s): ?>
        <div class="step <?= $s['ok'] === true ? 'ok' : ($s['ok'] === false ? 'bad' : 'skip') ?>">
            <div class="row">
                <span class="icon <?= $s['ok'] === true ? 'ok' : ($s['ok'] === false ? 'bad' : 'skip') ?>">
                    <?= $s['ok'] === true ? '✓' : ($s['ok'] === false ? '✗' : '–') ?>
                </span>
                <div style="flex:1">
                    <div class="t">#<?= $i + 1 ?> <?= e($s['title']) ?></div>
                    <div class="d"><?= e($s['detail']) ?></div>
                </div>
            </div>
        </div>
        <?php endforeach ?>

        <div class="tip">
            <b>下一步：</b><br>
            1. 如果以上全部通过，请访问 <a href="/game-chat">/game-chat</a> 查看聊天页面（刷新一次页面即可看到刚插入的测试消息）。<br>
            2. 如果"模型 timestamps 设置"那项红色 ❌，说明你服务器上的 GameChatMessage.php 还没更新，把最新的 <b>app/Models/GameChatMessage.php</b> 上传覆盖即可。<br>
            3. 如果"表不存在"那项红色 ❌，在 CMD 运行：<code style="background:#0f172a;padding:2px 6px;border-radius:4px">php artisan migrate --force</code>。<br>
            4. 如果"MC_SERVER_PATH 配置"红色 ❌，编辑网站根目录的 <b>.env</b> 文件，加一行：<code style="background:#0f172a;padding:2px 6px;border-radius:4px">MC_SERVER_PATH=C:\Users\Administrator\Desktop\server</code>（改成你实际的 MC 服务器根目录）。<br>
            5. 如果"日志文件读取权限"红色 ❌，给 Web 服务账户（IIS 的 IUSR，或 Apache 的 SYSTEM）对该日志文件的读取权限。
        </div>
    </body>
    </html>
    <?php
});

// 一键同步 MC 日志聊天记录（管理员才可以用）
Route::post('/chat-sync', function () {
    if (! auth()->check() || ! auth()->user()->isAdmin()) {
        return response()->json(['ok' => false, 'message' => '无权限'], 403);
    }
    try {
        $service = app(\App\Services\MinecraftLogSyncService::class);
        $result = $service->sync();
        return response()->json($result);
    } catch (Throwable $e) {
        return response()->json([
            'ok' => false,
            'message' => '异常：' . $e->getMessage(),
        ], 500);
    }
})->name('chat-sync')->middleware('auth');
