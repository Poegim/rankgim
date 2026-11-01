<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add last_seen_live_at to streamers so we can track when each
     * whitelisted channel was last broadcasting — populated by the
     * existing scheduler refresh, zero extra API calls.
     */
    public function up(): void
    {
        Schema::table('streamers', function (Blueprint $table) {
            $table->timestamp('last_seen_live_at')->nullable()->after('race');
        });
    }

    public function down(): void
    {
        Schema::table('streamers', function (Blueprint $table) {
            $table->dropColumn('last_seen_live_at');
        });
    }
};