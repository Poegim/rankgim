<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('forecast_matches', function (Blueprint $table) {
            $table->enum('winner_side', ['a', 'b'])->nullable()->after('winner_id');
        });
    }

    public function down(): void
    {
        Schema::table('forecast_matches', function (Blueprint $table) {
            $table->dropColumn('winner_side');
        });
    }
};
