<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('game_chat_messages', function (Blueprint $table) {
            $table->id();
            $table->string('player_uuid', 36)->nullable()->index();
            $table->string('player_name', 64)->index();
            $table->text('message');
            $table->string('channel', 32)->default('global');
            $table->timestamp('timestamp')->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('game_chat_messages');
    }
};
