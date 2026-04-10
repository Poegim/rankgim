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
        Schema::table('users', function (Blueprint $table) {
            // Per-type reminder preferences, replacing the single event_reminders flag
            $table->boolean('event_reminders_stream')->default(true)->after('event_reminders');
            $table->boolean('event_reminders_open')->default(true)->after('event_reminders_stream');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['event_reminders_stream', 'event_reminders_open']);
        });
    }
};
