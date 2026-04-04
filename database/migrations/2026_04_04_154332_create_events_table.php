<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->dateTime('starts_at');
            $table->string('timezone', 50)->default('UTC');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->json('links')->nullable();
            $table->boolean('is_online')->default(true);
            $table->string('location')->nullable();
            $table->timestamps();

            $table->index('starts_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};