<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class InstallDynmapForumTheme extends Command
{
    protected $signature = 'dynmap:install-forum-theme {--force : 覆盖已有主题文件} {--disable-webchat : 同时在 Dynmap 服务端禁用网页向游戏内发送消息}';
    protected $description = '将论坛主题安装到本机 Dynmap Web 目录';

    public function handle(): int
    {
        $source = resource_path('dynmap/forum-theme.css');
        $mcPath = rtrim((string) config('services.minecraft.log_path', ''), "\\/");
        $webPath = rtrim((string) config('services.minecraft.dynmap_web_path', ''), "\\/");
        if ($webPath === '') $webPath = $mcPath . DIRECTORY_SEPARATOR . 'plugins' . DIRECTORY_SEPARATOR . 'dynmap' . DIRECTORY_SEPARATOR . 'web';
        $targetDir = $webPath . DIRECTORY_SEPARATOR . 'css';
        $target = $targetDir . DIRECTORY_SEPARATOR . 'mc-forum-theme.css';
        $scriptSource = resource_path('dynmap/forum-theme.js');
        $scriptTarget = $targetDir . DIRECTORY_SEPARATOR . 'mc-forum-theme.js';
        // Service Worker 必须位于 Web 根目录，才能缓存 /tiles/ 下的地图瓦片。
        $workerSource = resource_path('dynmap/forum-theme-sw.js');
        $workerTarget = $webPath . DIRECTORY_SEPARATOR . 'mc-forum-theme-sw.js';
        $indexFile = $webPath . DIRECTORY_SEPARATOR . 'index.html';
        $themeVersion = substr(sha1_file($source) ?: (string) time(), 0, 12);
        $scriptVersion = substr(sha1_file($scriptSource) ?: (string) time(), 0, 12);
        // 版本参数强制浏览器取得新版资源，避免 Dynmap/手机浏览器继续使用旧主题缓存。
        $themeTag = '<link rel="stylesheet" href="css/mc-forum-theme.css?v=' . $themeVersion . '">';
        $scriptTag = '<script src="css/mc-forum-theme.js?v=' . $scriptVersion . '" defer></script>';

        if (! is_dir($webPath)) return $this->error('Dynmap Web 目录不存在：' . $webPath) ?: self::FAILURE;
        if (! is_file($indexFile) || ! is_readable($indexFile)) return $this->error('找不到 Dynmap 入口文件：' . $indexFile) ?: self::FAILURE;
        if (! is_dir($targetDir) && ! mkdir($targetDir, 0755, true) && ! is_dir($targetDir)) return $this->error('无法创建目录：' . $targetDir) ?: self::FAILURE;
        if ((! is_file($target) || $this->option('force')) && ! copy($source, $target)) return $this->error('写入主题失败：' . $target) ?: self::FAILURE;
        if ((! is_file($scriptTarget) || $this->option('force')) && ! copy($scriptSource, $scriptTarget)) return $this->error('写入功能限制脚本失败：' . $scriptTarget) ?: self::FAILURE;
        if ((! is_file($workerTarget) || $this->option('force')) && ! copy($workerSource, $workerTarget)) return $this->error('写入地图缓存脚本失败：' . $workerTarget) ?: self::FAILURE;

        $html = (string) file_get_contents($indexFile);
        $originalHtml = $html;
        // 每次安装均刷新已注入标签的版本号，确保 --force 的新版 CSS/JS 立即生效。
        $html = (string) preg_replace('/<link\s+[^>]*href=["\']css\/mc-forum-theme\.css(?:\?[^"\']*)?["\'][^>]*>/i', $themeTag, $html);
        $html = (string) preg_replace('/<script\s+[^>]*src=["\']css\/mc-forum-theme\.js(?:\?[^"\']*)?["\'][^>]*>\s*<\/script>/i', $scriptTag, $html);
        $needsCss = ! str_contains($html, 'mc-forum-theme.css');
        $needsScript = ! str_contains($html, 'mc-forum-theme.js');
        if ($needsCss || $needsScript) {
            $backup = $indexFile . '.mc-forum-theme.bak';
            if (! is_file($backup)) copy($indexFile, $backup);
            $tags = ($needsCss ? "    {$themeTag}\n" : '') . ($needsScript ? "    {$scriptTag}\n" : '');
            $updated = preg_replace('/<\/head\s*>/i', $tags . '</head>', $html, 1);
            if ($updated === null || $updated === $html || file_put_contents($indexFile, $updated) === false) {
                return $this->error('无法自动写入 index.html，请检查文件写入权限：' . $indexFile) ?: self::FAILURE;
            }
            $this->info('已自动注入论坛主题及功能限制脚本（原文件备份：' . $backup . '）');
        } else {
            if ($html !== $originalHtml && file_put_contents($indexFile, $html) === false) {
                return $this->error('无法更新 index.html 中的资源版本，请检查文件写入权限：' . $indexFile) ?: self::FAILURE;
            }
            $this->line('index.html 已包含论坛主题和功能限制脚本，资源版本已刷新。');
        }

        if ($this->option('disable-webchat')) {
            $configuration = $mcPath . DIRECTORY_SEPARATOR . 'plugins' . DIRECTORY_SEPARATOR . 'dynmap' . DIRECTORY_SEPARATOR . 'configuration.txt';
            if (! is_file($configuration) || ! is_readable($configuration) || ! is_writable($configuration)) {
                $this->warn('未修改网页聊天服务端开关：无法读写 ' . $configuration);
            } else {
                $config = (string) file_get_contents($configuration);
                $updatedConfig = preg_replace('/^(\s*allowwebchat\s*:\s*).*$/' . 'mi', '${1}false', $config, 1, $count);
                if (($count ?? 0) === 0) $updatedConfig .= "\nallowwebchat: false\n";
                if ($updatedConfig !== $config && file_put_contents($configuration, $updatedConfig) === false) {
                    $this->warn('网页聊天服务端开关写入失败：' . $configuration);
                } else {
                    $this->info('已在 Dynmap 配置中禁用网页向游戏内发送消息；重启 Minecraft 服务器后生效。');
                }
            }
        }

        $this->info('Dynmap 论坛主题安装完成：' . $target);
        return self::SUCCESS;
    }
}