<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class InstallDynmapForumTheme extends Command
{
    protected $signature = 'dynmap:install-forum-theme {--force : 覆盖已有主题文件}';
    protected $description = '将论坛主题 CSS 安装到本机 Dynmap Web 目录';

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
        $themeTag = '<link rel="stylesheet" href="css/mc-forum-theme.css">';
        $scriptTag = '<script src="css/mc-forum-theme.js" defer></script>';

        if (! is_dir($webPath)) return $this->error('Dynmap Web 目录不存在：' . $webPath) ?: self::FAILURE;
        if (! is_file($indexFile) || ! is_readable($indexFile)) return $this->error('找不到 Dynmap 入口文件：' . $indexFile) ?: self::FAILURE;
        if (! is_dir($targetDir) && ! mkdir($targetDir, 0755, true) && ! is_dir($targetDir)) return $this->error('无法创建目录：' . $targetDir) ?: self::FAILURE;
        if ((! is_file($target) || $this->option('force')) && ! copy($source, $target)) return $this->error('写入主题失败：' . $target) ?: self::FAILURE;
        if ((! is_file($scriptTarget) || $this->option('force')) && ! copy($scriptSource, $scriptTarget)) return $this->error('写入功能限制脚本失败：' . $scriptTarget) ?: self::FAILURE;
        if ((! is_file($workerTarget) || $this->option('force')) && ! copy($workerSource, $workerTarget)) return $this->error('写入地图缓存脚本失败：' . $workerTarget) ?: self::FAILURE;

        $html = (string) file_get_contents($indexFile);
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
            $this->line('index.html 已包含论坛主题和功能限制脚本。');
        }

        $this->info('Dynmap 论坛主题安装完成：' . $target);
        return self::SUCCESS;
    }
}