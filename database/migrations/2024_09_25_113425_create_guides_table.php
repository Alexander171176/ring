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
        Schema::create('guides', function (Blueprint $table) {
            $table->id();
            $table->integer('sort')->default(0); // Поле для хранения порядка сортировки
            $table->boolean('activity')->default(false); // Активность
            $table->string('title')->unique(); // Заголовок
            $table->text('url')->unique(); // Адрес
            $table->string('short')->nullable(); // Краткое Описание
            $table->text('description')->nullable(); // Описание
            $table->string('author')->nullable(); // Автор
            $table->string('tags')->nullable(); // Теги поста
            $table->unsignedBigInteger('views')->default(0); // Количество просмотров
            $table->unsignedBigInteger('likes')->default(0); // Количество лайков
            $table->text('image_url')->nullable()->default(''); // Адрес изображения
            $table->string('seo_title')->nullable()->default(''); // Title изображения
            $table->string('seo_alt')->nullable()->default(''); // Title изображения
            $table->string('meta_title')->nullable()->default(''); // meta title
            $table->string('meta_keywords')->nullable()->default(''); // meta keywords
            $table->string('meta_desc')->nullable()->default(''); // meta description
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('guides');
    }
};
