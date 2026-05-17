<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('soop_streamers');
    }

    /**
     * Re-create the old table on rollback so the data migration above can
     * be replayed. Schema mirrors the original create_soop_streamers_table.
     */
    public function down(): void
    {
        Schema::create('soop_streamers', function (Blueprint $table) {
            $table->id();
            $table->string('user_id')->unique();
            $table->string('label');
            $table->string('race')->nullable();
            $table->timestamps();
        });
    }
};