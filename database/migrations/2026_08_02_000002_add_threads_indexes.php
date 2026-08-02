<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('threads', function (Blueprint $table) {
            // 帖子列表排序核心：按最后回复时间/创建时间倒序
            $table->index('last_reply_at');

            // 置顶帖快速筛选
            $table->index('is_pinned');

            // 全文索引：搜索标题 + 内容（MySQL/PostgreSQL）
            // SQLite 不支持 FULLTEXT，用普通索引兜底
            if (Schema::getConnection()->getName() === 'mysql') {
                Schema::table('threads', function (Blueprint $table) {
                    $table->fullText(['title', 'body'], 'threads_title_body_fulltext');
                });
            } else {
                $table->index('title');
            }
        });

        // replies 表也需要补充常见查询索引
        Schema::table('replies', function (Blueprint $table) {
            $table->index('created_at');
            $table->index(['thread_id', 'created_at']);
        });

        // likes 表：点赞查询优化
        Schema::table('likes', function (Blueprint $table) {
            $table->index(['likeable_type', 'likeable_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::table('threads', function (Blueprint $table) {
            $table->dropIndex(['last_reply_at']);
            $table->dropIndex(['is_pinned']);
            if (Schema::getEngineerName() === 'mysql') {
                $table->dropIndex('threads_title_body_fulltext');
            } else {
                $table->dropIndex(['title']);
            }
        });

        Schema::table('replies', function (Blueprint $table) {
            $table->dropIndex(['created_at']);
            $table->dropIndex(['thread_id', 'created_at']);
        });

        Schema::table('likes', function (Blueprint $table) {
            $table->dropIndex(['likeable_type', 'likeable_id', 'user_id']);
        });
    }
};
