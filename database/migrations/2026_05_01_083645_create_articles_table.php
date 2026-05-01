<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('articles', function (Blueprint $table) {
            $table->id();
            // 'update' = auto-generated recalculation report
            // 'news'   = manually written news/announcement
            $table->string('type', 20)->default('news');
            $table->string('title');
            $table->string('slug')->unique();
            // Markdown source. Rendered to HTML via Article::getBodyHtmlAttribute().
            $table->longText('body');
            // Optional link back to the recalculation report that generated this article.
            $table->foreignId('recalculation_report_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();
            $table->dateTime('published_at')->nullable()->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('articles');
    }
};