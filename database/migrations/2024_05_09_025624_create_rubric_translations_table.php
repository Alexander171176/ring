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
        Schema::create('rubric_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rubric_id')->constrained('rubrics')->onDelete('cascade');
            $table->string('locale', 2); // Язык перевода (ru, en, kz)
            $table->string('title'); // Заголовок рубрики
            $table->string('url')->unique(); // Адрес рубрики
            $table->string('short')->nullable(); // Краткое Описание
            $table->text('description')->nullable(); // Описание рубрики
            $table->string('seo_title')->nullable(); // SEO Title
            $table->string('seo_alt')->nullable(); // SEO Alt
            $table->string('meta_title')->nullable(); // meta title
            $table->string('meta_keywords')->nullable(); // meta keywords
            $table->string('meta_desc')->nullable(); // meta description
            $table->timestamps();

            $table->unique(['rubric_id', 'locale']); // Гарантия уникальности перевода
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rubric_translations');
    }
};
