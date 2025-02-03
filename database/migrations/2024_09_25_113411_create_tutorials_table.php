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
        Schema::create('tutorials', function (Blueprint $table) {
            $table->id();
            $table->integer('sort')->default(0); // Поле для хранения порядка сортировки
            $table->boolean('activity')->default(false); // Активность
            $table->text('icon')->nullable(); // Иконка
            $table->string('title')->unique(); // Заголовок
            $table->text('url')->unique(); // Адрес
            $table->string('short')->nullable(); // Краткое Описание
            $table->text('description')->nullable(); // Описание
            $table->unsignedBigInteger('views')->default(0); // Количество просмотров
            $table->text('image_url')->nullable(); // Адрес изображения
            $table->string('seo_title')->nullable(); // Title изображения
            $table->string('seo_alt')->nullable(); // Title изображения
            $table->string('meta_title')->nullable(); // meta title
            $table->string('meta_keywords')->nullable(); // meta keywords
            $table->string('meta_desc')->nullable(); // meta description
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tutorials');
    }
};
