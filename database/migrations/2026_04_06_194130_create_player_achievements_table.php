<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('player_achievements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('player_id')->constrained()->cascadeOnDelete();
            // Unique achievement key matching config/achievements.php
            $table->string('key');
            // Tier at time of unlock: bronze, silver, gold, platinum, diamond
            $table->string('tier');
            // Optional value that triggered unlock, e.g. streak=50, rating=1500
            $table->unsignedInteger('value')->nullable();
            $table->date('unlocked_at');
            $table->timestamps();

            $table->unique(['player_id', 'key']);
            $table->index('player_id');
            $table->index('key');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('player_achievements');
    }
};