<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rating_histories', function (Blueprint $table) {
            $table->index('game_id');
            $table->index('result');
            $table->index(['played_at', 'player_id']);
        });
    }

    public function down(): void
    {
        Schema::table('rating_histories', function (Blueprint $table) {
            $table->dropIndex(['game_id']);
            $table->dropIndex(['result']);
            $table->dropIndex(['played_at', 'player_id']);
        });
    }
};
