<?php

namespace App\Console\Commands;

use App\Models\GameChatMessage;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class TestGameChat extends Command
{
    protected $signature = 'chat:test {message? : 要插入的测试消息内容}';

    protected $description = '向 game_chat_messages 表插入一条测试聊天消息，用于排查聊天功能';

    public function handle(): int
    {
        $message = $this->argument('message') ?? '这是一条来自 artisan 的测试消息 ' . now()->format('H:i:s');

        // 1. 检查表是否存在
        try {
            $exists = DB::getSchemaBuilder()->hasTable('game_chat_messages');
        } catch (\Throwable $e) {
            $this->error('检查表结构时出错：' . $e->getMessage());
            return self::FAILURE;
        }

        if (! $exists) {
            $this->error('表 game_chat_messages 不存在！请先运行：php artisan migrate');
            return self::FAILURE;
        }
        $this->info('✓ 表 game_chat_messages 存在');

        // 2. 输出当前连接信息
        try {
            $driver = DB::getDriverName();
            $database = DB::getDatabaseName();
            $this->info('✓ 数据库连接：' . $driver . ' / ' . $database);
        } catch (\Throwable $e) {
            $this->warn('获取数据库信息失败：' . $e->getMessage());
        }

        // 3. 查询当前行数
        try {
            $count = DB::table('game_chat_messages')->count();
            $this->info('✓ 当前表中有 ' . $count . ' 条记录');
        } catch (\Throwable $e) {
            $this->error('查询记录数失败：' . $e->getMessage());
            return self::FAILURE;
        }

        // 4. 尝试插入
        try {
            $created = GameChatMessage::addMessage('ConsoleTest', $message);
            $this->info('✓ 插入成功！新记录 ID = ' . $created->id);
            $this->line('  内容: ' . $created->message);
            $this->line('  时间: ' . $created->timestamp->format('Y-m-d H:i:s'));
        } catch (\Throwable $e) {
            $this->error('插入失败：' . $e->getMessage());
            $this->error('文件：' . $e->getFile() . ':' . $e->getLine());
            return self::FAILURE;
        }

        // 5. 再次查询确认
        try {
            $newCount = DB::table('game_chat_messages')->count();
            $this->info('✓ 插入后表中有 ' . $newCount . ' 条记录');
        } catch (\Throwable $e) {
            // ignore
        }

        return self::SUCCESS;
    }
}
