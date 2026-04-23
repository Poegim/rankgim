<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('forecast_seasons', function (Blueprint $table) {
            $table->id();
            $table->string('name');                          // e.g. "Season 1 — 2025"
            $table->dateTime('starts_at');
            $table->dateTime('ends_at')->nullable();         // null = season still active
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('forecast_seasons');
    }
};