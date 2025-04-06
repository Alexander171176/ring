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
        Schema::create('videos', function (Blueprint $table) {
            $table->id();
            $table->integer('sort')->default(0); // Поле для хранения порядка сортировки видео
            $table->boolean('activity')->default(false); // Активность видео

            $table->boolean('left')->default(false); // Показывать видео в левом сайдбаре
            $table->boolean('main')->default(false); // Показывать видео в главном экране
            $table->boolean('right')->default(false); // Показывать видео в правом сайдбаре

            $table->string('locale', 2); // Язык (ru, en, kz)

            $table->string('title')->unique(); // Заголовок видео
            $table->text('url')->unique(); // Адрес видео
            $table->string('short')->nullable(); // Краткое Описание
            $table->text('description')->nullable(); // Описание видео
            $table->string('author')->nullable(); // Автор видео
            $table->timestamp('published_at')->nullable(); // Дата и время публикации видео
            $table->unsignedInteger('duration')->nullable()->comment('Длительность видео в секундах');

            // Тип источника видео:
            // - local  : видео загружается на сайт,
            // - youtube: видео с YouTube,
            // - vimeo  : видео с Vimeo
            $table->enum('source_type', ['local', 'youtube', 'vimeo', 'code'])->default('local');

            // Для локальных видео можно хранить путь/URL файла,
            // а для внешних видео можно использовать данный столбец для хранения URL или оставить null
            $table->text('video_url')->nullable();

            // Идентификатор видео из внешнего сервиса (YouTube или Vimeo)
            $table->string('external_video_id')->nullable();

            $table->unsignedBigInteger('views')->default(0); // Количество просмотров видео
            $table->unsignedBigInteger('likes')->default(0); // Количество лайков видео

            $table->string('meta_title', 255)->nullable(); // meta title
            $table->string('meta_keywords', 255)->nullable(); // meta keywords
            $table->string('meta_desc', 255)->nullable(); // meta description

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('videos');
    }
};
