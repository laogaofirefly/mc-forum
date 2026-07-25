<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('server_status', function (Blueprint $table) {
            $table->id();
            $table->string('host');
            $table->integer('port')->default(25565);
            $table->boolean('is_online')->default(false);
            $table->integer('players_online')->default(0);
            $table->integer('players_max')->default(0);
            $table->string('motd')->nullable();
            $table->string('version')->nullable();
            $table->text('favicon')->nullable();
            $table->json('players_json')->nullable();
            $table->timestamp('updated_at')->nullable();

            $table->unique(['host', 'port']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('server_status');
    }
};
