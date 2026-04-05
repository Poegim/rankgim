<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('player_stats', function (Blueprint $table) {
            $table->unsignedBigInteger('player_id')->primary();
            $table->foreign('player_id')->references('id')->on('players')->cascadeOnDelete();
            $table->integer('peak_rating')->default(0);
            $table->integer('best_rank')->nullable();           // MIN(rank) from snapshots with correct filters
            $table->integer('current_streak')->default(0);     // positive = win streak, negative = loss streak
            $table->integer('longest_win_streak')->default(0);
            $table->timestamp('last_played_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('player_stats');
    }
};