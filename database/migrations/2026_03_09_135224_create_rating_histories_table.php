<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('rating_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('player_id')->constrained('players')->onDelete('cascade');
            $table->foreignId('game_id')->constrained('games')->onDelete('cascade');
            $table->integer('rating_before');
            $table->integer('rating_after');
            $table->integer('rating_change');
            $table->enum('result', ['win', 'loss', 'draw']);
            $table->dateTime('played_at');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rating_histories');
    }
};
