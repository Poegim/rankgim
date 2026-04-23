<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('forecast_predictions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('match_id')->constrained('forecast_matches')->cascadeOnDelete();
            $table->foreignId('wallet_id')->constrained('forecast_wallets')->cascadeOnDelete();
            $table->foreignId('pick_player_id')->constrained('players')->cascadeOnDelete(); // who user bet on

            $table->decimal('stake', 10, 2);                   // deducted from wallet immediately
            $table->decimal('odds_at_time', 6, 2);             // snapshot of odds when bet was placed
            $table->decimal('bonus_multiplier', 4, 2)->default(1.00); // currency bonus applied at bet time
            $table->decimal('potential_payout', 10, 2);        // stake * odds_at_time * bonus_multiplier
            $table->decimal('actual_payout', 10, 2)->nullable(); // null until settled

            $table->enum('result', ['pending', 'won', 'lost', 'refunded'])->default('pending');
            $table->dateTime('refunded_at')->nullable(); // set when match is soft-deleted

            $table->timestamps();

            // One prediction per user per match
            $table->unique(['user_id', 'match_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('forecast_predictions');
    }
};