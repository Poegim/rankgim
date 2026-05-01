<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('recalculation_reports', function (Blueprint $table) {
            $table->id();
            // When this recalculate run finished.
            $table->dateTime('recalculated_at')->index();
            // When the previous recalculate ran. Null for the first ever report.
            $table->dateTime('previous_recalculated_at')->nullable();
            // Full report payload: tournaments delta, risers, fallers, new players, totals.
            $table->json('summary');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recalculation_reports');
    }
};