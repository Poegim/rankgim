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
        Schema::create('rating_snapshots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('player_id')->constrained('players')->onDelete('cascade');
            $table->integer('rating');
            $table->integer('rank');
            $table->integer('games_played');
            $table->integer('wins');
            $table->integer('losses');
            $table->integer('draws');
            $table->date('snapshot_date');
            $table->timestamps();

            $table->unique(['player_id', 'snapshot_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rating_snapshots');
    }
};
