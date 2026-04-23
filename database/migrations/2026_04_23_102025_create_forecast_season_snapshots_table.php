<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('forecast_season_snapshots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('season_id')->constrained('forecast_seasons')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->enum('currency', ['minerals', 'khaydarin', 'biomass', 'credits']);

            // Profit = sum(actual_payout) - sum(stake) for settled predictions
            $table->decimal('final_profit', 10, 2)->default(0.00);
            $table->decimal('final_balance', 10, 2);            // wallet balance at season end
            $table->unsignedInteger('total_predictions')->default(0);
            $table->unsignedInteger('correct_predictions')->default(0);
            $table->unsignedInteger('rank');                     // rank at season end

            $table->timestamps();

            $table->unique(['season_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('forecast_season_snapshots');
    }
};