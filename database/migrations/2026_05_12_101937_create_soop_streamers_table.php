<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('soop_streamers', function (Blueprint $table) {
            $table->id();

            // SOOP user_id — the {user_id} segment in play.sooplive.com/{user_id}.
            // Unique because the same BJ should not be tracked twice.
            $table->string('user_id', 100)->unique();

            // Display name shown in the UI. Falls back to user_id if blank.
            $table->string('label', 100);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('soop_streamers');
    }
};