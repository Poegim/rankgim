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
        Schema::create('country_matchups', function (Blueprint $table) {
            $table->id();
            $table->string('winner_country', 2);
            $table->string('loser_country', 2);
            $table->integer('games')->default(0);
            $table->unique(['winner_country', 'loser_country']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('country_matchups');
    }
};
