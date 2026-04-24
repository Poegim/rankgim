<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('forecast_matches', function (Blueprint $table) {
            $table->enum('player_a_race', ['Terran', 'Zerg', 'Protoss', 'Random', 'Unknown'])
                ->default('Unknown')->change();
            $table->enum('player_b_race', ['Terran', 'Zerg', 'Protoss', 'Random', 'Unknown'])
                ->default('Unknown')->change();
        });
    }

    public function down(): void
    {
        Schema::table('forecast_matches', function (Blueprint $table) {
            $table->enum('player_a_race', ['Terran', 'Zerg', 'Protoss', 'Random', 'Unknown'])
                ->default(null)->change();
            $table->enum('player_b_race', ['Terran', 'Zerg', 'Protoss', 'Random', 'Unknown'])
                ->default(null)->change();
        });
    }
};
