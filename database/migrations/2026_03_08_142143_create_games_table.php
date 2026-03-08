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
        Schema::create('games', function (Blueprint $table) {
            $table->id();
            $table->foreignId('winner_id')->constrained('players')->onDelete('cascade');
            $table->foreignId('loser_id')->constrained('players')->onDelete('cascade');
            $table->foreignId('tournament_id')->constrained('tournaments')->onDelete('cascade');
            $table->dateTime('date_time')->nullable();

            // 1: winner, 2: loser, 3: draw
            $table->tinyInteger('result')->default(1)->comment('1: winner, 2: loser, 3: draw');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('games');
    }
};
