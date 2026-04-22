<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table) {
            // Guest players from config/events_guests.php (e.g. Koreans)
            // Stored as JSON array: [['name' => 'Flash', 'country_code' => 'KR', 'race' => 'Terran'], ...]
            $table->json('guest_players')->nullable()->after('links');
        });
    }

    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            if (Schema::hasColumn('events', 'guest_players')) {
                $table->dropColumn('guest_players');
            }
        });
    }
};