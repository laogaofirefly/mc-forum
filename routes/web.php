<?php

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\GameChatController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ImageUploadController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PlayerController;
use App\Http\Controllers\PrivateChatController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReplyController;
use App\Http\Controllers\ServerStatusController;
use App\Http\Controllers\ThreadController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::get('/register', [RegisterController::class, 'create'])->name('register');
Route::post('/register', [RegisterController::class, 'store']);

Route::get('/login', [LoginController::class, 'create'])->name('login');
Route::post('/login', [LoginController::class, 'store']);
Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');

// 同源首字母头像：避免 data: SVG URI 被浏览器或服务器安全策略拦截。
Route::get('/avatar/initial/{name}', function (string $name) {
    $name = rawurldecode(trim($name));
    $letter = mb_strtoupper(mb_substr($name, 0, 1)) ?: '?';
    $color = \App\Services\PlayerAvatarService::colorFor($name);
    $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="80" height="80" viewBox="0 0 80 80">'
        . '<rect width="80" height="80" fill="' . $color . '"/>'
        . '<text x="40" y="40" dy=".35em" text-anchor="middle" font-family="Arial,sans-serif" font-size="38" font-weight="700" fill="#fff">'
        . htmlspecialchars($letter, ENT_XML1 | ENT_QUOTES, 'UTF-8') . '</text></svg>';
    return response($svg, 200, ['Content-Type' => 'image/svg+xml; charset=UTF-8', 'Cache-Control' => 'public, max-age=86400']);
})->where('name', '.*')->name('avatar.initial');

Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit')->middleware('auth');
Route::post('/profile/edit', [ProfileController::class, 'update'])->name('profile.update')->middleware('auth');
Route::post('/profile/avatar', [ProfileController::class, 'avatar'])->name('profile.avatar')->middleware('auth');
Route::post("/profile/chat-bg", [ProfileController::class, "chatBg"])->name("profile.chat-bg")->middleware("auth");
Route::post("/profile/chat-bg/remove", [ProfileController::class, "chatBgRemove"])->name("profile.chat-bg.remove")->middleware("auth");
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

// 通知中心
Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index')->middleware('auth');
Route::get('/notifications/unread-count', [NotificationController::class, 'unreadCount'])->name('notifications.unread')->middleware('auth');

// 图片上传（发帖/回复插图）
Route::post('/upload-image', [ImageUploadController::class, 'upload'])->name('upload-image')->middleware('auth');

Route::get('/game-chat', [GameChatController::class, 'index'])->name('game-chat');
Route::get('/game-chat/fetch', [GameChatController::class, 'fetch'])->name('game-chat.fetch');
Route::post('/game-chat/send', [GameChatController::class, 'send'])->name('game-chat.send')->middleware('auth');
Route::post('/game-chat/demo', [GameChatController::class, 'demo'])->name('game-chat.demo')->middleware('auth');

// 用户私聊
Route::middleware('auth')->group(function () {
    Route::get('/private-chat', [PrivateChatController::class, 'index'])->name('private-chat');
    Route::get('/private-chat/fetch', [PrivateChatController::class, 'fetch'])->name('private-chat.fetch');
    Route::post('/private-chat/send', [PrivateChatController::class, 'send'])->name('private-chat.send');
    Route::get('/private-chat/contacts', [PrivateChatController::class, 'contacts'])->name('private-chat.contacts');
    Route::get('/private-chat/search-users', [PrivateChatController::class, 'searchUsers'])->name('private-chat.search-users');
});

// MC 服务器成员名单
Route::get('/players', [PlayerController::class, 'index'])->name('players.index');

// Dynmap 原生地图：Laravel 从同机 Dynmap 读取数据，再由论坛页面渲染，避免 iframe 限制。
Route::get('/map', function () {
    $dynmapUrl = rtrim(trim((string) config('services.minecraft.dynmap_url', '')), '/');
    if ($dynmapUrl === '' || ! filter_var($dynmapUrl, FILTER_VALIDATE_URL) || ! in_array(parse_url($dynmapUrl, PHP_URL_SCHEME), ['http', 'https'], true)) {
        abort(503, '在线地图暂未配置');
    }

    return view('dynmap.index', ['dynmapUrl' => $dynmapUrl]);
})->name('dynmap.index');

Route::get('/map/data', function () {
    $dynmapUrl = rtrim(trim((string) config('services.minecraft.dynmap_url', '')), '/');
    if ($dynmapUrl === '' || ! filter_var($dynmapUrl, FILTER_VALIDATE_URL) || ! in_array(parse_url($dynmapUrl, PHP_URL_SCHEME), ['http', 'https'], true)) {
        return response()->json(['ok' => false, 'message' => '在线地图暂未配置'], 503);
    }

    try {
        $data = Cache::remember('dynmap.component.data', now()->addSeconds(3), function () use ($dynmapUrl) {
            // 网站与 MC 同机时优先从 Dynmap 的 web 目录读取，避免 Web 服务端口、防火墙或绑定地址影响。
            $mcPath = rtrim((string) config('services.minecraft.log_path', ''), '\\/');
            $webPath = rtrim((string) config('services.minecraft.dynmap_web_path', ''), '\\/');
            if ($webPath === '') $webPath = $mcPath . DIRECTORY_SEPARATOR . 'plugins' . DIRECTORY_SEPARATOR . 'dynmap' . DIRECTORY_SEPARATOR . 'web';
            // Dynmap 3.x/4.x 的输出格式有差异：配置有时在 standalone，有时直接在 web 根目录。
            $localCandidates = [
                $webPath . DIRECTORY_SEPARATOR . 'standalone' . DIRECTORY_SEPARATOR . 'dynmap_config.json',
                $webPath . DIRECTORY_SEPARATOR . 'standalone' . DIRECTORY_SEPARATOR . 'dynmap_world.json',
                // 很多 Dynmap 版本使用 JS 包装数据，而不是 .json 文件。
                $webPath . DIRECTORY_SEPARATOR . 'standalone' . DIRECTORY_SEPARATOR . 'config.js',
                $webPath . DIRECTORY_SEPARATOR . 'standalone' . DIRECTORY_SEPARATOR . 'dynmap_config.js',
                $webPath . DIRECTORY_SEPARATOR . 'dynmap_config.json',
                $webPath . DIRECTORY_SEPARATOR . 'dynmap_world.json',
                $webPath . DIRECTORY_SEPARATOR . 'config.js',
            ];
            $decodeDynmapData = static function (string $contents): ?array {
                $contents = preg_replace('/^\xEF\xBB\xBF/', '', trim($contents));
                $json = json_decode($contents, true);
                if (is_array($json)) return $json;

                // config.js 在不同 Dynmap 版本中可能带注释、尾部脚本或 var config = 前缀。
                // 不依赖“文件必须以 }; 结尾”的正则，直接提取首个完整 JSON 对象。
                $start = strcspn($contents, '{[');
                if ($start >= strlen($contents)) return null;
                $open = $contents[$start];
                $close = $open === '{' ? '}' : ']';
                $depth = 0; $quoted = false; $escaped = false;
                for ($i = $start, $len = strlen($contents); $i < $len; $i++) {
                    $char = $contents[$i];
                    if ($quoted) {
                        if ($escaped) { $escaped = false; continue; }
                        if ($char === '\\') { $escaped = true; continue; }
                        if ($char === '"') $quoted = false;
                        continue;
                    }
                    if ($char === '"') { $quoted = true; continue; }
                    if ($char === $open) $depth++;
                    if ($char === $close && --$depth === 0) {
                        $json = json_decode(substr($contents, $start, $i - $start + 1), true);
                        return is_array($json) ? $json : null;
                    }
                }
                return null;
            };
            foreach ($localCandidates as $file) {
                if (is_file($file) && is_readable($file)) {
                    $json = $decodeDynmapData((string) file_get_contents($file));
                    if (is_array($json)) return $json;
                }
            }

            // 兜底：即使 config.js 没有世界数组，也可以从已渲染的 tiles 目录构造原生地图数据。
            $tilesPath = $webPath . DIRECTORY_SEPARATOR . 'tiles';
            if (is_dir($tilesPath) && is_readable($tilesPath)) {
                $worlds = [];
                foreach (new \DirectoryIterator($tilesPath) as $worldDir) {
                    if (! $worldDir->isDir() || $worldDir->isDot()) continue;
                    $maps = [];
                    foreach (new \DirectoryIterator($worldDir->getPathname()) as $mapDir) {
                        if ($mapDir->isDir() && ! $mapDir->isDot()) {
                            $maps[] = ['name' => $mapDir->getFilename(), 'prefix' => $mapDir->getFilename(), 'title' => $mapDir->getFilename()];
                        }
                    }
                    if ($maps) $worlds[] = ['name' => $worldDir->getFilename(), 'title' => $worldDir->getFilename(), 'maps' => $maps];
                }
                if ($worlds) return ['worlds' => $worlds, 'source' => 'local-tiles'];
            }

            $attempts = [];
            foreach (['/standalone/dynmap_config.json', '/standalone/dynmap_world.json', '/standalone/config.js', '/standalone/dynmap_config.js'] as $path) {
                try {
                    $response = Http::connectTimeout(2)->timeout(5)->get($dynmapUrl . $path);
                    $attempts[] = $path . ' (HTTP ' . $response->status() . ')';
                    if ($response->successful() && ($json = $decodeDynmapData($response->body()))) return $json;
                } catch (\Throwable $e) {
                    $attempts[] = $path . ' (' . $e->getMessage() . ')';
                }
            }
            throw new \RuntimeException('Dynmap 未返回可识别的数据。已尝试本地目录：' . $webPath . '；HTTP：' . implode('，', $attempts));
        });

        return response()->json(['ok' => true, 'data' => $data])
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate');
    } catch (\Throwable $e) {
        report($e);
        return response()->json(['ok' => false, 'message' => $e->getMessage()], 502);
    }
})->name('dynmap.data');

// Dynmap 原始 config.js 同源代理：其内容可能是 JavaScript 而非严格 JSON，交由浏览器原生执行。
Route::get('/map/config-script', function () {
    $dynmapUrl = rtrim(trim((string) config('services.minecraft.dynmap_url', '')), '/');
    $mcPath = rtrim((string) config('services.minecraft.log_path', ''), '\\/');
    $webPath = rtrim((string) config('services.minecraft.dynmap_web_path', ''), '\\/');
    if ($webPath === '') $webPath = $mcPath . DIRECTORY_SEPARATOR . 'plugins' . DIRECTORY_SEPARATOR . 'dynmap' . DIRECTORY_SEPARATOR . 'web';
    $localFile = $webPath . DIRECTORY_SEPARATOR . 'standalone' . DIRECTORY_SEPARATOR . 'config.js';
    try {
        if (is_file($localFile) && is_readable($localFile)) {
            $body = (string) file_get_contents($localFile);
        } else {
            $response = Http::connectTimeout(2)->timeout(5)->get($dynmapUrl . '/standalone/config.js');
            if (! $response->successful()) abort(502, 'Dynmap config.js 不可访问');
            $body = $response->body();
        }
        return response($body, 200, ['Content-Type' => 'application/javascript; charset=UTF-8', 'Cache-Control' => 'no-store']);
    } catch (\Throwable $e) {
        report($e);
        abort(502, 'Dynmap config.js 加载失败');
    }
})->name('dynmap.config-script');

// 返回指定图层中真实存在的瓦片文件名。不同 Dynmap 版本的瓦片目录层级不同，前端不再猜测文件名。
Route::get('/map/tiles-manifest/{world}/{map}', function (string $world, string $map) {
    if (preg_match('/^[A-Za-z0-9_-]+$/', $world) !== 1 || preg_match('/^[A-Za-z0-9_-]+$/', $map) !== 1) abort(404);
    $mcPath = rtrim((string) config('services.minecraft.log_path', ''), '\\/');
    $webPath = rtrim((string) config('services.minecraft.dynmap_web_path', ''), '\\/');
    if ($webPath === '') $webPath = $mcPath . DIRECTORY_SEPARATOR . 'plugins' . DIRECTORY_SEPARATOR . 'dynmap' . DIRECTORY_SEPARATOR . 'web';
    $base = $webPath . DIRECTORY_SEPARATOR . 'tiles' . DIRECTORY_SEPARATOR . $world . DIRECTORY_SEPARATOR . $map;
    if (! is_dir($base) || ! is_readable($base)) return response()->json(['ok' => true, 'tiles' => []]);

    $tiles = [];
    $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($base, \FilesystemIterator::SKIP_DOTS));
    foreach ($iterator as $file) {
        if (! $file->isFile() || ! in_array(strtolower($file->getExtension()), ['png', 'jpg', 'jpeg', 'webp'], true)) continue;
        $path = str_replace(DIRECTORY_SEPARATOR, '/', substr($file->getPathname(), strlen($base) + 1));
        $tiles[] = $path;
        if (count($tiles) >= 2500) break;
    }
    return response()->json(['ok' => true, 'tiles' => $tiles])->header('Cache-Control', 'no-store');
})->name('dynmap.tiles-manifest');

// 使用查询参数代理瓦片，避免 Windows/Dynmap 的嵌套瓦片路径中的 / 被 Laravel 路由截断。
Route::get('/map/tile-file', function (\Illuminate\Http\Request $request) {
    $world = (string) $request->query('world', '');
    $map = (string) $request->query('map', '');
    $tile = str_replace('\\', '/', (string) $request->query('path', ''));
    $safeSegment = static fn (string $value): bool => preg_match('/^[A-Za-z0-9_-]+$/', $value) === 1;
    if (! $safeSegment($world) || ! $safeSegment($map) || str_contains($tile, '..') || preg_match('/^[A-Za-z0-9_\/-]+\.(png|jpe?g|webp)$/i', $tile) !== 1) abort(404);
    $mcPath = rtrim((string) config('services.minecraft.log_path', ''), '\\/');
    $webPath = rtrim((string) config('services.minecraft.dynmap_web_path', ''), '\\/');
    if ($webPath === '') $webPath = $mcPath . DIRECTORY_SEPARATOR . 'plugins' . DIRECTORY_SEPARATOR . 'dynmap' . DIRECTORY_SEPARATOR . 'web';
    $file = $webPath . DIRECTORY_SEPARATOR . 'tiles' . DIRECTORY_SEPARATOR . $world . DIRECTORY_SEPARATOR . $map . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $tile);
    if (! is_file($file) || ! is_readable($file)) abort(404);
    $mime = function_exists('mime_content_type') ? (mime_content_type($file) ?: 'application/octet-stream') : 'application/octet-stream';
    return response((string) file_get_contents($file), 200, ['Content-Type' => $mime, 'Cache-Control' => 'public, max-age=300']);
})->name('dynmap.tile-file');

// 原生地图瓦片代理：兼容简单路径的旧接口。
Route::get('/map/tile/{world}/{map}/{tile}', function (string $world, string $map, string $tile) {
    $dynmapUrl = rtrim(trim((string) config('services.minecraft.dynmap_url', '')), '/');
    $safeSegment = static fn (string $value): bool => preg_match('/^[A-Za-z0-9_-]+$/', $value) === 1;
    if ($dynmapUrl === '' || ! $safeSegment($world) || ! $safeSegment($map)
        || str_contains($tile, '..') || preg_match('/^[A-Za-z0-9_\/-]+\.(png|jpe?g|webp)$/i', $tile) !== 1) {
        abort(404);
    }

    try {
        // 同机部署优先读取磁盘瓦片，避免 Dynmap web 端口不可访问时原生地图失效。
        $mcPath = rtrim((string) config('services.minecraft.log_path', ''), '\\/');
        $webPath = rtrim((string) config('services.minecraft.dynmap_web_path', ''), '\\/');
        if ($webPath === '') $webPath = $mcPath . DIRECTORY_SEPARATOR . 'plugins' . DIRECTORY_SEPARATOR . 'dynmap' . DIRECTORY_SEPARATOR . 'web';
        $localTile = $webPath . DIRECTORY_SEPARATOR . 'tiles' . DIRECTORY_SEPARATOR . $world . DIRECTORY_SEPARATOR . $map . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $tile);
        if (is_file($localTile) && is_readable($localTile)) {
            $mime = function_exists('mime_content_type') ? (mime_content_type($localTile) ?: 'application/octet-stream') : 'application/octet-stream';
            return response((string) file_get_contents($localTile), 200, ['Content-Type' => $mime, 'Cache-Control' => 'public, max-age=300']);
        }

        $response = Http::connectTimeout(2)->timeout(5)->get($dynmapUrl . '/tiles/' . rawurlencode($world) . '/' . rawurlencode($map) . '/' . $tile);
        if (! $response->successful()) {
            abort($response->status() === 404 ? 404 : 502);
        }
        return response($response->body(), 200, [
            'Content-Type' => $response->header('Content-Type', 'application/octet-stream'),
            'Cache-Control' => 'public, max-age=300',
        ]);
    } catch (\Throwable $e) {
        report($e);
        abort(502, '地图瓦片加载失败');
    }
})->where(['world' => '[A-Za-z0-9_-]+', 'map' => '[A-Za-z0-9_-]+', 'tile' => '[A-Za-z0-9_\\/-]+\\.png'])->name('dynmap.tile');

// 服务器监控已合并到控制台，保留路由重定向
Route::get('/admin/monitor', function () {
    return redirect()->route('admin.console');
})->name('admin.monitor')->middleware('auth');
Route::get('/admin/console', function () {
    if (! auth()->check() || ! auth()->user()->isAdmin()) {
        abort(403, '仅管理员可访问');
    }
    return view('admin.console');
})->name('admin.console')->middleware('auth');

// 控制台日志读取 API
Route::get('/admin/console/log', function (\Illuminate\Http\Request $request) {
    if (! auth()->check() || ! auth()->user()->isAdmin()) {
        return response()->json(['ok' => false, 'message' => '仅管理员可访问'], 403);
    }

    $mcPath = config('services.minecraft.log_path', '');
    if (empty($mcPath)) {
        return response()->json(['ok' => false, 'message' => '未配置 MC_SERVER_PATH']);
    }
    $logPath = rtrim($mcPath, '\\/') . DIRECTORY_SEPARATOR . 'logs' . DIRECTORY_SEPARATOR . 'latest.log';
    if (! file_exists($logPath) || ! is_readable($logPath)) {
        return response()->json(['ok' => false, 'message' => '日志文件不存在或不可读: ' . $logPath]);
    }

    $fileSize = filesize($logPath);
    $after = (int) $request->input('after', 0);
    $limit = min(500, max(10, (int) $request->input('lines', 200)));

    // 如果 after 超过文件大小，说明没有新内容
    if ($after >= $fileSize) {
        return response()->json(['ok' => true, 'lines' => [], 'size' => $fileSize, 'pos' => $fileSize]);
    }

    $handle = fopen($logPath, 'rb');
    if (! $handle) {
        return response()->json(['ok' => false, 'message' => '无法打开日志文件']);
    }

    // 如果是首次加载 (after=0)，从文件末尾往前读
    if ($after === 0 && $fileSize > 0) {
        $startPos = max(0, $fileSize - 131072); // 最多读最后 128KB
        fseek($handle, $startPos);
        // 跳过可能不完整的第一行
        if ($startPos > 0) {
            fgets($handle);
        }
    } else {
        fseek($handle, $after);
    }

    $lines = [];
    // 解析器只创建一次，避免每一行都解析 Laravel 容器造成控制台首次加载缓慢。
    $chatParser = app(\App\Services\MinecraftLogSyncService::class);
    $pos = ftell($handle);
    $lineNum = 0;

    while (($line = fgets($handle, 8192)) !== false) {
        $lineNum++;
        $pos = ftell($handle);
        $raw = rtrim($line, "\r\n");

        // 解析是否为聊天消息
        $chat = null;
        $parsed = $chatParser->parseLine($line);
        if ($parsed) {
            $chat = ['player' => $parsed['player'], 'message' => $parsed['message']];
        }

        $lines[] = [
            'n' => $lineNum,
            'raw' => $raw,
            'chat' => $chat,
        ];

        if (count($lines) >= $limit) break;
    }

    fclose($handle);

    return response()->json([
        'ok' => true,
        'lines' => $lines,
        'size' => $fileSize,
        'pos' => $pos,
        'log_path' => $logPath,
    ]);
})->name('admin.console.log')->middleware('auth');

// 下载当前 MC 世界存档（管理员）。使用 PHP ZipArchive 在 Windows 下直接打包。
Route::get('/admin/console/world-download', function () {
    if (! auth()->check() || ! auth()->user()->isAdmin()) abort(403);
    if (! class_exists(\ZipArchive::class)) return response('服务器未启用 PHP zip 扩展，无法下载存档。', 500);
    $mcPath = rtrim((string) config('services.minecraft.log_path', env('MC_SERVER_PATH')), '\\/');
    if ($mcPath === '' || ! is_dir($mcPath)) return response('MC_SERVER_PATH 目录不存在。', 404);
    $worldName = 'world';
    $properties = $mcPath . DIRECTORY_SEPARATOR . 'server.properties';
    if (is_readable($properties)) {
        foreach (file($properties, FILE_IGNORE_NEW_LINES) ?: [] as $line) {
            if (str_starts_with(trim($line), 'level-name=')) { $worldName = trim(substr(trim($line), 11)); break; }
        }
    }
    $worldName = basename(str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $worldName));
    $worldPath = $mcPath . DIRECTORY_SEPARATOR . $worldName;
    if (! is_dir($worldPath)) return response('未找到世界存档目录：' . $worldName, 404);
    $temp = tempnam(sys_get_temp_dir(), 'mc-world-');
    if ($temp === false) return response('无法创建临时下载文件。', 500);
    @unlink($temp);
    $zipPath = $temp . '.zip';
    try {
        $zip = new \ZipArchive();
        if ($zip->open($zipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) throw new \RuntimeException('无法创建 ZIP 文件。');
        $root = rtrim($worldPath, '\\/') . DIRECTORY_SEPARATOR;
        $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($worldPath, \FilesystemIterator::SKIP_DOTS));
        foreach ($iterator as $file) {
            if ($file->isFile()) $zip->addFile($file->getPathname(), $worldName . '/' . substr($file->getPathname(), strlen($root)));
        }
        $zip->close();
        return response()->download($zipPath, $worldName . '-backup-' . now()->format('Ymd-His') . '.zip')->deleteFileAfterSend(true);
    } catch (\Throwable $e) {
        @unlink($zipPath);
        return response('存档打包失败：' . $e->getMessage(), 500);
    }
})->name('admin.console.world-download')->middleware('auth');

// 服务器状态检测 API
Route::get('/admin/console/status', function () {
    if (! auth()->check() || ! auth()->user()->isAdmin()) {
        return response()->json(['ok' => false, 'message' => '仅管理员可访问'], 403);
    }

    $rconHost = config('services.minecraft.rcon.host', '127.0.0.1');
    $rconPort = (int) config('services.minecraft.rcon.port', 25575);
    $rconPassword = config('services.minecraft.rcon.password', '');
    $mcHost = config('services.minecraft.host', '127.0.0.1');
    $mcPort = (int) config('services.minecraft.port', 25565);

    $running = false;
    $method = '';

    // 方法1：尝试 RCON 连接
    if (! empty($rconPassword)) {
        try {
            $rcon = new \App\Services\MinecraftRconService($rconHost, $rconPort, $rconPassword, 2);
            $rcon->connect();
            $rcon->disconnect();
            $running = true;
            $method = 'rcon';
        } catch (\Throwable $e) {
            // RCON 连不上，继续尝试其他方法
        }
    }

    // 方法2：检查 MC 端口是否开放
    if (! $running) {
        $socket = @fsockopen($mcHost, $mcPort, $errno, $errstr, 2);
        if ($socket) {
            fclose($socket);
            $running = true;
            $method = 'port';
        }
    }

    // 方法3：检查 Java 进程
    if (! $running) {
        if (PHP_OS_FAMILY === 'Windows') {
            @exec('tasklist /FI "IMAGENAME eq javaw.exe" 2>&1', $output, $code);
            $running = ($code === 0 && ! empty($output) && ! str_contains(implode(' ', $output), 'INFO: No tasks'));
        } else {
            @exec('pgrep -f "java.*(minecraft|server\.jar|paper\.jar|spigot\.jar|purpur\.jar|fabric)" 2>&1', $output, $code);
            $running = ($code === 0 && ! empty($output) && ! empty(trim($output[0] ?? '')));
        }
        if ($running) $method = 'process';
    }

    return response()->json([
        'ok' => true,
        'running' => $running,
        'method' => $method,
    ]);
})->name('admin.console.status')->middleware('auth');

// 服务器配置读取 API
Route::get('/admin/console/config', function () {
    if (! auth()->check() || ! auth()->user()->isAdmin()) {
        return response()->json(['ok' => false, 'message' => '仅管理员可访问'], 403);
    }
    return response()->json([
        'ok' => true,
        'config' => [
            'mc_server_path' => config('services.minecraft.log_path', ''),
            'mc_host' => config('services.minecraft.host', '127.0.0.1'),
            'mc_port' => config('services.minecraft.port', 25565),
            'query_port' => config('services.minecraft.query_port', 25565),
            'rcon_host' => config('services.minecraft.rcon.host', '127.0.0.1'),
            'rcon_port' => config('services.minecraft.rcon.port', '25575'),
            'rcon_password' => config('services.minecraft.rcon.password', ''),
            'start_command' => config('services.minecraft.start_command', ''),
            'stop_command' => config('services.minecraft.stop_command', 'stop'),
            'java_path' => config('services.minecraft.java_path', 'java'),
            'java_xms' => config('services.minecraft.java_xms', '1G'),
            'java_xmx' => config('services.minecraft.java_xmx', '4G'),
            'auto_restart' => config('services.minecraft.auto_restart', false),
            'backup_path' => config('services.minecraft.backup_path', ''),
        ],
    ]);
})->name('admin.console.config')->middleware('auth');

// 服务器配置更新 API
Route::post('/admin/console/config', function (\Illuminate\Http\Request $request) {
    if (! auth()->check() || ! auth()->user()->isAdmin()) {
        return response()->json(['ok' => false, 'message' => '仅管理员可操作'], 403);
    }

    $request->validate([
        'mc_server_path' => 'nullable|string|max:500',
        'mc_host' => 'nullable|string|max:255',
        'mc_port' => 'nullable|string|max:10',
        'query_port' => 'nullable|string|max:10',
        'rcon_host' => 'nullable|string|max:255',
        'rcon_port' => 'nullable|string|max:10',
        'rcon_password' => 'nullable|string|max:255',
        'start_command' => 'nullable|string|max:1000',
        'stop_command' => 'nullable|string|max:200',
        'java_path' => 'nullable|string|max:500',
        'java_xms' => 'nullable|string|max:20',
        'java_xmx' => 'nullable|string|max:20',
        'auto_restart' => 'nullable|string|max:10',
        'backup_path' => 'nullable|string|max:500',
    ]);

    $envPath = base_path('.env');
    if (! file_exists($envPath) || ! is_writable($envPath)) {
        return response()->json(['ok' => false, 'message' => '.env 文件不可写，请检查权限'], 500);
    }

    $content = file_get_contents($envPath);
    $updated = [];

    $map = [
        'MC_SERVER_PATH' => $request->input('mc_server_path', ''),
        'MINECRAFT_SERVER_HOST' => $request->input('mc_host', '127.0.0.1'),
        'MINECRAFT_SERVER_PORT' => $request->input('mc_port', '25565'),
        'MC_QUERY_PORT' => $request->input('query_port', '25565'),
        'MC_RCON_HOST' => $request->input('rcon_host', '127.0.0.1'),
        'MC_RCON_PORT' => $request->input('rcon_port', '25575'),
        'MC_RCON_PASSWORD' => $request->input('rcon_password', ''),
        'MC_START_COMMAND' => $request->input('start_command', ''),
        'MC_STOP_COMMAND' => $request->input('stop_command', 'stop'),
        'MC_JAVA_PATH' => $request->input('java_path', 'java'),
        'MC_JAVA_XMS' => $request->input('java_xms', '1G'),
        'MC_JAVA_XMX' => $request->input('java_xmx', '4G'),
        'MC_AUTO_RESTART' => $request->input('auto_restart', 'false'),
        'MC_BACKUP_PATH' => $request->input('backup_path', ''),
    ];

    foreach ($map as $key => $value) {
        $updated[$key] = $value;
        $pattern = '/^' . preg_quote($key, '/') . '=.*$/m';
        $line = $key . '=' . $value;
        if (preg_match($pattern, $content)) {
            $content = preg_replace($pattern, $line, $content);
        } else {
            $content .= "\n" . $line;
        }
    }

    file_put_contents($envPath, $content);

    // 同步运行时配置，避免需要重启服务器
    config()->set('services.minecraft.log_path', $request->input('mc_server_path', ''));
    config()->set('services.minecraft.host', $request->input('mc_host', '127.0.0.1'));
    config()->set('services.minecraft.port', (int) $request->input('mc_port', 25565));
    config()->set('services.minecraft.query_port', (int) $request->input('query_port', 25565));
    config()->set('services.minecraft.rcon.host', $request->input('rcon_host', '127.0.0.1'));
    config()->set('services.minecraft.rcon.port', (int) $request->input('rcon_port', 25575));
    config()->set('services.minecraft.rcon.password', $request->input('rcon_password', ''));
    config()->set('services.minecraft.start_command', $request->input('start_command', ''));
    config()->set('services.minecraft.stop_command', $request->input('stop_command', 'stop'));
    config()->set('services.minecraft.java_path', $request->input('java_path', 'java'));
    config()->set('services.minecraft.java_xms', $request->input('java_xms', '1G'));
    config()->set('services.minecraft.java_xmx', $request->input('java_xmx', '4G'));
    config()->set('services.minecraft.auto_restart', $request->input('auto_restart', 'false'));
    config()->set('services.minecraft.backup_path', $request->input('backup_path', ''));

    // 清除 opcache
    if (function_exists('opcache_reset')) @opcache_reset();

    return response()->json([
        'ok' => true,
        'message' => '配置已保存，如已修改路径或密码，请刷新页面后重试相关功能',
        'updated' => $updated,
    ]);
})->name('admin.console.config.update')->middleware('auth');

// 启动 MC 服务器 API
Route::post('/admin/console/start', function () {
    if (! auth()->check() || ! auth()->user()->isAdmin()) {
        return response()->json(['ok' => false, 'message' => '仅管理员可操作'], 403);
    }

    $command = trim((string) config('services.minecraft.start_command', ''));
    $mcPath = config('services.minecraft.log_path', '');
    $cwd = ! empty($mcPath) ? rtrim((string) $mcPath, '\\/') : null;
    // 未显式配置时自动识别常见启动脚本或服务端 JAR，仍可直接从网页启动。
    if ($command === '' && $cwd && is_dir($cwd)) {
        if (PHP_OS_FAMILY === 'Windows' && is_file($cwd . DIRECTORY_SEPARATOR . 'start.bat')) {
            $command = 'cmd /c start.bat';
        } elseif (PHP_OS_FAMILY !== 'Windows' && is_file($cwd . DIRECTORY_SEPARATOR . 'start.sh')) {
            $command = 'bash start.sh';
        } else {
            $jars = glob($cwd . DIRECTORY_SEPARATOR . '*.jar') ?: [];
            $preferred = array_values(array_filter($jars, fn ($jar) => preg_match('/(server|paper|purpur|spigot|fabric|forge)/i', basename($jar))));
            $jar = $preferred[0] ?? ($jars[0] ?? null);
            if ($jar) {
                $jarName = basename($jar);
                $command = PHP_OS_FAMILY === 'Windows'
                    ? 'cmd /c start "" /b java -Xms1G -Xmx2G -jar "' . $jarName . '" nogui'
                    : 'java -Xms1G -Xmx2G -jar ' . escapeshellarg($jarName) . ' nogui';
            }
        }
    }
    if ($command === '') {
        return response()->json(['ok' => false, 'message' => '未找到启动命令。请在 .env 设置 MC_START_COMMAND，或在 MC_SERVER_PATH 放置 start.sh/start.bat 或服务端 JAR']);
    }

    // 先检查是否已在运行
    $rconHost = config('services.minecraft.rcon.host', '127.0.0.1');
    $rconPort = (int) config('services.minecraft.rcon.port', 25575);
    $rconPassword = config('services.minecraft.rcon.password', '');
    $mcHost = config('services.minecraft.host', '127.0.0.1');
    $mcPort = (int) config('services.minecraft.port', 25565);

    $alreadyRunning = false;
    if (! empty($rconPassword)) {
        try {
            $rcon = new \App\Services\MinecraftRconService($rconHost, $rconPort, $rconPassword, 2);
            $rcon->connect();
            $rcon->disconnect();
            $alreadyRunning = true;
        } catch (\Throwable $e) {}
    }
    if (! $alreadyRunning) {
        $socket = @fsockopen($mcHost, $mcPort, $errno, $errstr, 2);
        if ($socket) { fclose($socket); $alreadyRunning = true; }
    }

    if ($alreadyRunning) {
        return response()->json(['ok' => false, 'message' => '服务器已在运行中，无需重复启动']);
    }

    // 执行启动命令（后台运行）
    $mcPath = config('services.minecraft.log_path', '');
    $cwd = ! empty($mcPath) ? rtrim($mcPath, '\\/') : null;

    try {
        $descriptors = [
            0 => ['pipe', 'r'],
            1 => ['pipe', 'w'],
            2 => ['pipe', 'w'],
        ];
        if ($cwd && ! is_dir($cwd)) {
            return response()->json(['ok' => false, 'message' => 'MC_SERVER_PATH 目录不存在：' . $cwd], 500);
        }
        $process = proc_open($command, $descriptors, $pipes, $cwd);
        if (! is_resource($process)) {
            return response()->json(['ok' => false, 'message' => '无法执行启动命令']);
        }

        // 关闭所有管道，让进程脱离
        foreach ($pipes as $pipe) fclose($pipe);
        proc_close($process);

        return response()->json([
            'ok' => true,
            'message' => '启动命令已发送，请等待 10-30 秒后刷新状态',
            'command' => $command,
            'cwd' => $cwd,
        ]);
    } catch (\Throwable $e) {
        return response()->json(['ok' => false, 'message' => '启动失败：' . $e->getMessage()], 500);
    }
})->name('admin.console.start')->middleware('auth');

// ── server.properties 读写 ──
Route::get('/admin/console/properties', function () {
    if (! auth()->check() || ! auth()->user()->isAdmin()) abort(403);
    $mcPath = config('services.minecraft.log_path', '');
    if (empty($mcPath)) return response()->json(['ok' => false, 'message' => '未配置 MC_SERVER_PATH'], 200);
    $file = rtrim((string) $mcPath, '\\/') . '/server.properties';
    if (! file_exists($file) || ! is_readable($file)) return response()->json(['ok' => false, 'message' => '文件不可读：' . $file], 200);
    $props = [];
    foreach (file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [] as $line) {
        $t = trim($line);
        if ($t === '' || str_starts_with($t, '#')) continue;
        $eq = strpos($t, '=');
        if ($eq === false) continue;
        $props[trim(substr($t, 0, $eq))] = trim(substr($t, $eq + 1));
    }
    return response()->json(['ok' => true, 'properties' => $props, 'path' => $file]);
})->name('admin.console.properties')->middleware('auth');

Route::post('/admin/console/properties', function (\Illuminate\Http\Request $r) {
    if (! auth()->check() || ! auth()->user()->isAdmin()) abort(403);
    $mcPath = config('services.minecraft.log_path', '');
    if (empty($mcPath)) return response()->json(['ok' => false, 'message' => '未配置 MC_SERVER_PATH'], 500);
    $propPath = rtrim((string) $mcPath, '\\/') . '/server.properties';
    if (! file_exists($propPath)) return response()->json(['ok' => false, 'message' => '文件不存在：' . $propPath], 200);
    if (! is_writable($propPath)) return response()->json(['ok' => false, 'message' => '无写入权限：' . $propPath], 200);

    $updates = $r->input('updates', []);
    if (! is_array($updates) || empty($updates)) return response()->json(['ok' => false, 'message' => '无修改'], 200);

    $bak = $propPath . '.bak.' . time();
    copy($propPath, $bak);

    $lines = file($propPath);
    $updated = [];
    foreach ($lines as $i => &$line) {
        $t = trim($line);
        if (str_starts_with($t, '#')) continue;
        $eq = strpos($t, '=');
        if ($eq === false || $eq === 0) continue;
        $k = trim(substr($t, 0, $eq));
        if (array_key_exists($k, $updates)) {
            $line = $k . '=' . trim((string) $updates[$k]) . "\n";
            $updated[$k] = trim((string) $updates[$k]);
        }
    }
    foreach ($updates as $k => $v) {
        if (! array_key_exists($k, $updated)) { $lines[] = $k . '=' . trim((string) $v) . "\n"; $updated[$k] = trim((string) $v); }
    }
    if (file_put_contents($propPath, implode('', $lines)) === false) {
        copy($bak, $propPath);
        return response()->json(['ok' => false, 'message' => '写入失败，已恢复'], 500);
    }
    return response()->json(['ok' => true, 'message' => '已保存 ' . count($updated) . ' 项（需重启服务器生效）', 'updated' => $updated, 'backup' => basename($bak)]);
})->name('admin.console.properties.save')->middleware('auth');

// ── 世界存档下载 ──
Route::get('/admin/console/world-download', function () {
    if (! auth()->check() || ! auth()->user()->isAdmin()) abort(403);

    $mcPath = config('services.minecraft.log_path', '');
    if (empty($mcPath)) return response()->json(['ok' => false, 'message' => '未配置 MC_SERVER_PATH'], 500);
    $basePath = rtrim((string) $mcPath, '\\/');

    // 从 server.properties 读取 world 文件夹名
    $propFile = $basePath . '/server.properties';
    $worldName = 'world'; // 默认
    if (file_exists($propFile)) {
        foreach (file($propFile) ?: [] as $line) {
            $t = trim($line);
            if (str_starts_with($t, 'level-name=')) {
                $worldName = trim(substr($t, strlen('level-name=')));
                break;
            }
        }
    }
    $worldPath = $basePath . '/' . $worldName;
    if (! is_dir($worldPath)) {
        return response()->json(['ok' => false, 'message' => '世界存档目录不存在：' . $worldPath], 404);
    }

    // 创建临时 zip 文件
    $tempDir = sys_get_temp_dir();
    $zipName = $worldName . '_' . date('Ymd_His') . '.zip';
    $zipPath = $tempDir . '/' . $zipName;

    // 使用 shell zip 命令（比 PHP ZipArchive 更省内存，适合大文件）
    $cmd = sprintf(
        'cd %s && zip -r -q %s %s 2>&1',
        escapeshellarg($basePath),
        escapeshellarg($zipPath),
        escapeshellarg($worldName)
    );
    exec($cmd, $output, $exitCode);

    if ($exitCode !== 0 || ! file_exists($zipPath)) {
        return response()->json(['ok' => false, 'message' => '压缩失败，请检查磁盘空间和权限'], 500);
    }

    $fileSize = filesize($zipPath);

    // 流式下载，下载完成后自动删除临时文件
    return response()->streamDownload(function () use ($zipPath) {
        $handle = fopen($zipPath, 'rb');
        while (! feof($handle)) {
            echo fread($handle, 8192);
            flush();
        }
        fclose($handle);
        // 下载完成后删除临时文件
        @unlink($zipPath);
    }, $zipName, [
        'Content-Type' => 'application/zip',
        'Content-Length' => $fileSize,
    ]);
})->name('admin.console.world-download')->middleware('auth');

// 控制台系统监控指标 API
Route::get('/admin/console/metrics', function () {
    if (! auth()->check() || ! auth()->user()->isAdmin()) {
        return response()->json(['ok' => false, 'message' => '仅管理员可访问'], 403);
    }

    $data = [
        'ok' => true,
        'time' => now()->toDateTimeString(),
        'php_memory_usage' => round(memory_get_usage(true) / 1024 / 1024, 2),
        'php_memory_peak' => round(memory_get_peak_usage(true) / 1024 / 1024, 2),
    ];

    // 系统负载
    if (strtoupper(substr(PHP_OS, 0, 3)) !== 'WIN' && function_exists('sys_getloadavg')) {
        $data['load'] = sys_getloadavg();
    } else {
        $data['load'] = null;
    }

    // 磁盘空间
    $diskFree = @disk_free_space(base_path());
    $diskTotal = @disk_total_space(base_path());
    if ($diskFree !== false && $diskTotal !== false) {
        $data['disk'] = [
            'free' => round($diskFree / 1024 / 1024 / 1024, 2),
            'total' => round($diskTotal / 1024 / 1024 / 1024, 2),
            'used' => round(($diskTotal - $diskFree) / 1024 / 1024 / 1024, 2),
            'percent' => $diskTotal > 0 ? round(($diskTotal - $diskFree) * 100 / $diskTotal, 1) : 0,
        ];
    } else {
        $data['disk'] = null;
    }

    // 系统环境
    $data['system'] = [
        'os' => php_uname('s') . ' ' . php_uname('r'),
        'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? php_uname('s'),
        'php_version' => PHP_VERSION,
        'sapi' => PHP_SAPI,
        'laravel_version' => app()->version(),
        'db_driver' => config('database.default'),
        'mc_host' => config('services.minecraft.host', 'localhost'),
        'mc_port' => (int) config('services.minecraft.port', 25565),
        'timezone' => config('app.timezone'),
        'php_memory_limit' => ini_get('memory_limit'),
        'php_upload_max' => ini_get('upload_max_filesize'),
        'php_post_max' => ini_get('post_max_size'),
        'php_max_exec' => ini_get('max_execution_time') . 's',
    ];

    // 应用统计
    $data['app'] = [
        'today_threads' => \App\Models\Thread::where('created_at', '>=', now()->startOfDay())->count(),
        'today_users' => \App\Models\User::where('created_at', '>=', now()->startOfDay())->count(),
        'total_threads' => \App\Models\Thread::count(),
        'total_users' => \App\Models\User::count(),
    ];

    return response()->json($data);
})->name('admin.console.metrics')->middleware('auth');

// 管理员用户管理（列表、详情、封禁、解封）
Route::middleware('auth')->prefix('admin/users')->name('admin.users.')->group(function () {
    Route::get('/', [\App\Http\Controllers\Admin\UserController::class, 'index'])->name('index');
    Route::get('/{user}', [\App\Http\Controllers\Admin\UserController::class, 'show'])->name('show');
    Route::match(['post', 'patch'], '/{user}/block', [\App\Http\Controllers\Admin\UserController::class, 'block'])->name('block');
    Route::match(['post', 'patch'], '/{user}/unblock', [\App\Http\Controllers\Admin\UserController::class, 'unblock'])->name('unblock');
});

// 管理员命令控制台：向 MC 服务器发送任意命令
Route::post('/admin/rcon', function (\Illuminate\Http\Request $request) {
    if (! auth()->check() || ! auth()->user()->isAdmin()) {
        return response()->json(['ok' => false, 'message' => '仅管理员可执行命令'], 403);
    }

    $request->validate([
        'command' => 'required|string|min:1|max:200',
    ]);

    $raw = trim($request->input('command'));
    // 去掉开头的 /（如果用户加了）
    $command = ltrim($raw, '/');

    $rconHost = config('services.minecraft.rcon.host', '127.0.0.1');
    $rconPort = (int) config('services.minecraft.rcon.port', 25575);
    $rconPassword = config('services.minecraft.rcon.password', '');

    if (empty($rconPassword)) {
        return response()->json(['ok' => false, 'message' => 'RCON 未配置：请在 .env 里设置 MC_RCON_PASSWORD']);
    }

    try {
        $rcon = new \App\Services\MinecraftRconService($rconHost, $rconPort, $rconPassword, 3);
        $rcon->connect();
        $response = $rcon->sendCommand($command);
        $rcon->disconnect();

        return response()->json([
            'ok' => true,
            'command' => '/' . $command,
            'response' => $response !== '' ? $response : '（命令执行成功，服务器无返回文本）',
            'time' => now()->format('H:i:s'),
            'user' => auth()->user()->name,
        ]);
    } catch (\Throwable $e) {
        return response()->json([
            'ok' => false,
            'message' => '执行失败：' . $e->getMessage(),
        ], 500);
    }
})->name('admin.rcon')->middleware('auth');

Route::get('/api/server-status', [ServerStatusController::class, 'index'])->name('server-status');

// 临时：成员列表诊断页（访问 /players-test 查看原始数据，定位重复玩家）
Route::get('/players-test', function () {
    $basePath = rtrim((string) config('services.minecraft.log_path', ''), '\\/');
    $out = [];

    $out[] = '=== 配置 ===';
    $out[] = 'MC_SERVER_PATH / log_path = ' . ($basePath === '' ? '(未配置)' : $basePath);
    $out[] = '';

    if ($basePath === '') {
        $out[] = '未配置路径，无法诊断。';
        return response(implode("\n", $out), 200, ['Content-Type' => 'text/plain; charset=UTF-8']);
    }

    // usercache.json 原始内容
    $usercachePath = $basePath . DIRECTORY_SEPARATOR . 'usercache.json';
    $out[] = '=== usercache.json ===';
    $out[] = '路径: ' . $usercachePath;
    if (! is_file($usercachePath)) {
        $out[] = '文件不存在';
    } elseif (! is_readable($usercachePath)) {
        $out[] = '文件不可读';
    } else {
        $raw = @file_get_contents($usercachePath);
        $data = json_decode($raw ?: '', true);
        if (! is_array($data)) {
            $out[] = '解析失败，原始内容前 500 字符: ' . substr((string) $raw, 0, 500);
        } else {
            $out[] = '共 ' . count($data) . ' 条记录:';
            foreach ($data as $i => $entry) {
                $name = $entry['name'] ?? '?';
                $uuid = $entry['uuid'] ?? '?';
                $norm = strtolower(str_replace('-', '', (string) $uuid));
                $out[] = sprintf('  [%d] name=%s | uuid=%s | 规范化=%s', $i, $name, $uuid, $norm);
            }
        }
    }
    $out[] = '';

    // whitelist.json 原始内容
    $whitelistPath = $basePath . DIRECTORY_SEPARATOR . 'whitelist.json';
    $out[] = '=== whitelist.json ===';
    $out[] = '路径: ' . $whitelistPath;
    if (! is_file($whitelistPath)) {
        $out[] = '文件不存在';
    } elseif (! is_readable($whitelistPath)) {
        $out[] = '文件不可读';
    } else {
        $raw = @file_get_contents($whitelistPath);
        $data = json_decode($raw ?: '', true);
        if (! is_array($data)) {
            $out[] = '解析失败，原始内容前 500 字符: ' . substr((string) $raw, 0, 500);
        } else {
            $out[] = '共 ' . count($data) . ' 条记录:';
            foreach ($data as $i => $entry) {
                $name = $entry['name'] ?? '?';
                $uuid = $entry['uuid'] ?? '?';
                $norm = strtolower(str_replace('-', '', (string) $uuid));
                $out[] = sprintf('  [%d] name=%s | uuid=%s | 规范化=%s', $i, $name, $uuid, $norm);
            }
        }
    }
    $out[] = '';

    // 检查是否有其他可能包含玩家数据的文件
    $out[] = '=== 服务器根目录下含 player 的文件 ===';
    $candidates = ['usercache.json', 'whitelist.json', 'ops.json', 'banned-players.json', 'banned-ips.json', 'knownplayers.json'];
    foreach ($candidates as $f) {
        $p = $basePath . DIRECTORY_SEPARATOR . $f;
        $exists = is_file($p);
        $out[] = '  ' . $f . ': ' . ($exists ? '存在 (' . filesize($p) . ' bytes)' : '不存在');
    }
    $out[] = '';

    // 调用实际服务看最终结果
    $out[] = '=== 服务最终返回的成员列表 ===';
    $svc = new \App\Services\MinecraftPlayerService();
    $result = $svc->getAllPlayers();
    $out[] = '总数: ' . $result['total'];
    $out[] = '状态: ' . ($result['ok'] ? 'OK' : '失败 - ' . $result['message']);
    foreach ($result['players'] as $p) {
        $out[] = sprintf('  - %s (uuid=%s)', $p['name'], $p['uuid']);
    }
    $out[] = '';

    // 找出重复的名字（大小写不敏感）
    $out[] = '=== 名字重复检查（大小写不敏感）===';
    $names = [];
    foreach ($result['players'] as $p) {
        $key = strtolower($p['name']);
        $names[$key][] = ['name' => $p['name'], 'uuid' => $p['uuid']];
    }
    $hasDup = false;
    foreach ($names as $key => $entries) {
        if (count($entries) > 1) {
            $hasDup = true;
            $out[] = '名字 [' . $key . '] 出现 ' . count($entries) . ' 次:';
            foreach ($entries as $e) {
                $out[] = '  - ' . $e['name'] . ' (uuid=' . $e['uuid'] . ')';
            }
        }
    }
    if (! $hasDup) {
        $out[] = '没有发现名字重复';
    }

    return response(implode("\n", $out), 200, ['Content-Type' => 'text/plain; charset=UTF-8']);
});

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

    // 10. RCON 配置检查（网站→游戏 发消息需要）
    $rconHost = config('services.minecraft.rcon.host', '127.0.0.1');
    $rconPort = (int) config('services.minecraft.rcon.port', 25575);
    $rconPassword = config('services.minecraft.rcon.password', '');

    if (empty($rconPassword)) {
        $steps[] = [
            'ok' => false,
            'title' => 'RCON 配置 (网站→游戏)',
            'detail' => "未配置！网站向游戏发消息需要 RCON。\n请在 .env 文件加 3 行：\nMC_RCON_HOST=127.0.0.1\nMC_RCON_PORT=25575\nMC_RCON_PASSWORD=你设置的强密码\n\n同时 MC 服务器的 server.properties 里要有：\nenable-rcon=true\nrcon.port=25575\nrcon.password=同上\nserver-ip=127.0.0.1",
        ];
    } else {
        $steps[] = [
            'ok' => true,
            'title' => 'RCON 配置 (网站→游戏)',
            'detail' => "已配置：{$rconHost}:{$rconPort}（密码已设置）",
        ];
    }

    // 11. RCON 连接测试
    if (! empty($rconPassword)) {
        try {
            $rcon = new \App\Services\MinecraftRconService($rconHost, $rconPort, $rconPassword, 3);
            $rcon->connect();
            $rcon->disconnect();
            $steps[] = [
                'ok' => true,
                'title' => 'RCON 连接测试',
                'detail' => "连接成功 ✓ 可以从网站向游戏发消息了",
            ];
        } catch (Throwable $e) {
            $steps[] = [
                'ok' => false,
                'title' => 'RCON 连接测试',
                'detail' => '失败：' . $e->getMessage() . "\n\n常见原因：\n1. MC 服务器没开 RCON（检查 server.properties 里 enable-rcon=true）\n2. 密码不对\n3. 端口不对（默认 25575）\n4. MC 服务器没运行\n5. 改了 server.properties 后没重启 MC 服务器",
            ];
        }
    } else {
        $steps[] = [
            'ok' => null,
            'title' => 'RCON 连接测试',
            'detail' => '前面 RCON 配置未通过，跳过',
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

// 同步 MC 日志聊天记录（登录用户都可以触发，因为这是聊天页后台自动同步用的）
Route::post('/chat-sync', function () {
    if (! auth()->check()) {
        return response()->json(['ok' => false, 'message' => '无权限'], 403);
    }
    try {
        $service = app(\App\Services\MinecraftLogSyncService::class);
        $result = $service->setMaxBatch(50)->sync();
        return response()->json($result);
    } catch (Throwable $e) {
        return response()->json([
            'ok' => false,
            'message' => '异常：' . $e->getMessage(),
        ], 500);
    }
})->name('chat-sync')->middleware('auth');

// 日志预览：直接读取 MC 服务器日志最后 30 行并标注哪些会被识别为聊天
Route::get('/chat-log-preview', function () {
    if (! auth()->check() || ! auth()->user()->isAdmin()) {
        abort(403);
    }
    $mcPath = config('services.minecraft.log_path', '');
    $logPath = $mcPath ? (rtrim($mcPath, '\\/') . DIRECTORY_SEPARATOR . 'logs' . DIRECTORY_SEPARATOR . 'latest.log') : '';
    $rows = [];
    $error = null;

    if (empty($mcPath)) {
        $error = '未配置 MC_SERVER_PATH';
    } elseif (! file_exists($logPath)) {
        $error = '日志文件不存在：' . $logPath;
    } elseif (! is_readable($logPath)) {
        $error = '日志文件不可读：' . $logPath;
    } else {
        $lines = [];
        $handle = fopen($logPath, 'rb');
        if ($handle !== false) {
            while (($line = fgets($handle, 4096)) !== false) {
                $lines[] = rtrim($line, "\r\n");
                if (count($lines) > 200) {
                    array_shift($lines);
                }
            }
            fclose($handle);
        }
        // 取最后 30 行
        $tail = array_slice($lines, -30);
        $service = app(\App\Services\MinecraftLogSyncService::class);
        foreach ($tail as $line) {
            $parsed = $service->parseLine($line);
            $rows[] = [
                'raw' => $line,
                'is_chat' => $parsed !== null,
                'parsed' => $parsed,
            ];
        }
    }

    return response()->json([
        'ok' => $error === null,
        'error' => $error,
        'log_path' => $logPath,
        'rows' => $rows,
    ]);
})->name('chat-log-preview')->middleware('auth');
// ========== 点赞（Like）路由 ==========
Route::post('/likes/toggle', [\App\Http\Controllers\LikeController::class, 'toggle'])->name('likes.toggle')->middleware('auth');

// ========== @提及用户名自动补全（AJAX） ==========
Route::get('/users/search-mention', function (\Illuminate\Http\Request $request) {
    if (! auth()->check()) {
        return response()->json([]);
    }
    $q = trim($request->input('q', ''));
    if (mb_strlen($q) < 1) {
        return response()->json([]);
    }
    $users = \App\Models\User::where('name', 'like', $q . '%')
        ->where('is_blocked', false)
        ->limit(10)
        ->get(['id', 'name', 'avatar']);
    return response()->json($users);
})->name('users.search')->middleware('auth');