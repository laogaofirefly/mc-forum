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

        if (! is_dir($webPath)) return $this->error('Dynmap Web 目录不存在：' . $webPath) ?: self::FAILURE;
        if (! is_dir($targetDir) && ! mkdir($targetDir, 0755, true) && ! is_dir($targetDir)) return $this->error('无法创建目录：' . $targetDir) ?: self::FAILURE;
        if (is_file($target) && ! $this->option('force')) return $this->warn('主题已存在，使用 --force 覆盖：' . $target) ?: self::SUCCESS;
        if (! copy($source, $target)) return $this->error('写入主题失败：' . $target) ?: self::FAILURE;

        $this->info('主题已安装：' . $target);
        $this->line('请在 Dynmap 的 web/index.html 的 </head> 前加入：<link rel="stylesheet" href="css/mc-forum-theme.css">');
        return self::SUCCESS;
    }
}