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
        Schema::table('forecast_predictions', function (Blueprint $table) {
            $table->enum('pick_side', ['a', 'b'])->nullable()->after('pick_player_id');
            $table->foreignId('pick_player_id')->nullable()->change(); // make nullable for non-foreigner
        });
    }

    public function down(): void
    {
        Schema::table('forecast_predictions', function (Blueprint $table) {
            $table->dropColumn('pick_side');
        });
    }
};
