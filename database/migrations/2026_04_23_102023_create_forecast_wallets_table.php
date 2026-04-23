<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('forecast_wallets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('season_id')->constrained('forecast_seasons')->cascadeOnDelete();
            $table->enum('currency', ['minerals', 'khaydarin', 'biomass', 'credits']);
            $table->decimal('balance', 10, 2)->default(50.00);
            $table->unsignedInteger('resets_count')->default(0); // track how many times user reset
            $table->timestamps();

            // One wallet per user per season
            $table->unique(['user_id', 'season_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('forecast_wallets');
    }
};