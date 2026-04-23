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
        Schema::table('forecast_matches', function (Blueprint $table) {
            // Allow null for foreigner player IDs — korean/clan/national use name strings instead
            $table->string('player_a_name')->nullable()->after('player_b_id');
            $table->string('player_b_name')->nullable()->after('player_a_name');
            $table->string('player_a_country')->nullable()->after('player_b_name'); // national only
            $table->string('player_b_country')->nullable()->after('player_a_country');

            // Make player FKs nullable
            $table->foreignId('player_a_id')->nullable()->change();
            $table->foreignId('player_b_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('forecast_matches', function (Blueprint $table) {
            $table->dropColumn(['player_a_name', 'player_b_name', 'player_a_country', 'player_b_country']);
            $table->foreignId('player_a_id')->nullable(false)->change();
            $table->foreignId('player_b_id')->nullable(false)->change();
        });
    }
};
