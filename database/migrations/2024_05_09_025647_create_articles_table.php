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
        Schema::create('articles', function (Blueprint $table) {
            $table->id();
            $table->integer('sort')->default(0); // Поле для хранения порядка сортировки постов
            $table->boolean('activity')->default(false); // Активность поста
            $table->string('title')->unique(); // Заголовок поста
            $table->text('url')->unique(); // Адрес поста
            $table->string('short')->nullable(); // Краткое Описание поста
            $table->text('description')->nullable(); // Описание поста
            $table->string('author')->nullable(); // Автор поста
            $table->string('tags')->nullable(); // Теги поста
            $table->unsignedBigInteger('views')->default(0); // Количество просмотров поста
            $table->unsignedBigInteger('likes')->default(0); // Количество лайков поста
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
        Schema::dropIfExists('articles');
    }
};
