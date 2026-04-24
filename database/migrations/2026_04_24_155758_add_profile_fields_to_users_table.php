<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // ISO 2-letter country code, e.g. 'PL', 'DE'
            $table->string('country_code', 2)->nullable()->after('profile_photo_path');
            // Free-text city, e.g. 'Warsaw'
            $table->string('city', 100)->nullable()->after('country_code');
            // Short bio / about text
            $table->string('bio', 280)->nullable()->after('city');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['country_code', 'city', 'bio']);
        });
    }
};