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
        Schema::table('player_achievements', function (Blueprint $table) {
            $table->unsignedInteger('owners_count')->default(0)->after('unlocked_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('player_achievements', function (Blueprint $table) {
            $table->dropColumn('owners_count');
        });
    }
};
