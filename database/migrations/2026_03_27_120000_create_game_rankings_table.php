<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('game_rankings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('player_name', 10);
            $table->integer('score');
            $table->unsignedBigInteger('play_time');
            $table->timestamps();

            $table->index('score', 'game_rankings_score_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('game_rankings');
    }
};
