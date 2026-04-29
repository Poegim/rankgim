<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Number of times the user has logged in
            $table->unsignedInteger('login_count')->default(0)->after('remember_token');
            // Timestamp of the most recent successful login
            $table->timestamp('last_login_at')->nullable()->after('login_count');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['login_count', 'last_login_at']);
        });
    }
};