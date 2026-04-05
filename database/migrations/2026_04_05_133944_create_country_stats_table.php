<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('country_stats', function (Blueprint $table) {
            $table->string('country_code', 2)->primary();
            $table->string('country');
            $table->integer('player_count')->default(0);
            $table->integer('avg_rating')->default(0);      // average rating of top 5 players
            $table->integer('total_wins')->default(0);      // wins from ALL country players in last 12m
            $table->integer('total_losses')->default(0);
            $table->integer('win_ratio')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('country_stats');
    }
};