<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('forecast_matches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('season_id')->constrained('forecast_seasons')->cascadeOnDelete();

            // Optional link to an event — null means standalone match
            $table->foreignId('event_id')->nullable()->constrained('events')->nullOnDelete();

            // Players — snapshot their race at match creation time for currency bonus logic
            $table->foreignId('player_a_id')->constrained('players')->cascadeOnDelete();
            $table->foreignId('player_b_id')->constrained('players')->cascadeOnDelete();
            $table->enum('player_a_race', ['Terran', 'Zerg', 'Protoss', 'Random', 'Unknown']);
            $table->enum('player_b_race', ['Terran', 'Zerg', 'Protoss', 'Random', 'Unknown']);

            // Odds calculated from ELO difference (foreigner) or set manually (korean/clan/national)
            $table->decimal('odds_a', 6, 2);
            $table->decimal('odds_b', 6, 2);

            // Flat multiplier for non-foreigner matches (admin sets this manually)
            $table->decimal('multiplier', 4, 2)->default(1.00);

            $table->enum('match_type', ['foreigner', 'korean', 'clan', 'national']);

            $table->dateTime('scheduled_at');

            // Betting closes 1h before match by default — admin can override
            $table->dateTime('locked_at');

            // Settlement — null until moderator resolves the match
            $table->foreignId('winner_id')->nullable()->constrained('players')->nullOnDelete();
            $table->dateTime('settled_at')->nullable();
            $table->foreignId('settled_by')->nullable()->constrained('users')->nullOnDelete();

            $table->softDeletes(); // soft delete triggers refund via observer
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('forecast_matches');
    }
};