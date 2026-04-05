<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // player_a_id is always LEAST(id1, id2), player_b_id always GREATEST — ensures unique pairs
        Schema::create('head_to_head', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('player_a_id');
            $table->unsignedBigInteger('player_b_id');
            $table->integer('games_count')->default(0);
            $table->integer('player_a_wins')->default(0);
            $table->foreign('player_a_id')->references('id')->on('players')->cascadeOnDelete();
            $table->foreign('player_b_id')->references('id')->on('players')->cascadeOnDelete();
            $table->unique(['player_a_id', 'player_b_id']);
            $table->index('player_a_id');
            $table->index('player_b_id');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('head_to_head');
    }
};