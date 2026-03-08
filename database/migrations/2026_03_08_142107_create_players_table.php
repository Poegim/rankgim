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
        Schema::create('players', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('country')->default('Unknown');
            $table->string('country_code')->default('XX');
            $table->enum('race', ['Terran', 'Zerg', 'Protoss', 'Random', 'Unknown'])->default('Unknown');
            
            // If its just AKA.
            $table->foreignId('player_id')
                ->nullable()
                ->constrained('players')
                ->cascadeOnDelete();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('players');
    }
};
