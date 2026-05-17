<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('streamers', function (Blueprint $table) {
            $table->id();

            // Which platform this entry belongs to.
            // 'soop'   — SOOP Open API user_id (numeric-as-string, e.g. "afstar1")
            // 'twitch' — Twitch login string (e.g. "zzzeropl")
            $table->string('platform', 16);

            // Platform-specific identifier (no overlap guaranteed between platforms,
            // so we scope uniqueness with the composite index below).
            $table->string('user_id');

            // Display label shown on stream cards (e.g. "ASL Official", "Bisu").
            $table->string('label');

            // Optional race tag — drives the race filter on /streams.
            // Nullable for non-player streams (official channels, tournament casters).
            $table->string('race')->nullable();

            $table->timestamps();

            // Each (platform, user_id) pair can only be whitelisted once.
            $table->unique(['platform', 'user_id']);

            // Common query: "give me all SOOP streamers" — fast lookup.
            $table->index('platform');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('streamers');
    }
};