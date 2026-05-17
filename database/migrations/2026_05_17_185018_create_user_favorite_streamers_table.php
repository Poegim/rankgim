<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_favorite_streamers', function (Blueprint $table) {
            $table->id();

            // Logged-in user who favorited this streamer.
            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();

            // Platform discriminator — matches streamers.platform values.
            $table->string('platform', 16);

            // Platform-specific identifier — same shape as streamers.user_id.
            // Intentionally NOT a foreign key to streamers.id, because a user
            // can favorite a streamer who isn't on the admin whitelist.
            // (favorites are personal, whitelist is global)
            $table->string('streamer_user_id');

            $table->timestamps();

            // One favorite per user/platform/streamer combination.
            $table->unique(['user_id', 'platform', 'streamer_user_id'], 'user_fav_streamers_unique');

            // Hot query path: "what does this user have favorited?"
            $table->index(['user_id', 'platform'], 'user_fav_streamers_user_platform_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_favorite_streamers');
    }
};