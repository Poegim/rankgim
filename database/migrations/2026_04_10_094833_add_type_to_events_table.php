<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Adds event type distinction:
     *  - stream: watch-only event (live stream, ongoing tournament broadcast)
     *  - open:   playable tournament, registration open until starts_at
     */
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table) {
            // Event type: 'stream' (watch only) or 'open' (register & play)
            $table->string('type', 20)->default('stream')->after('name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn('type');
        });
    }
};