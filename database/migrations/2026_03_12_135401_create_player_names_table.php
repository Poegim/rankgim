<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('player_names', function (Blueprint $table) {
            $table->id();
            $table->foreignId('player_id')->constrained('players')->cascadeOnDelete();
            $table->string('name');
            $table->boolean('is_primary')->default(false);
            $table->index('name');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('player_names');
    }
};