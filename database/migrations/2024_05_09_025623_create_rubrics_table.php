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
        Schema::create('rubrics', function (Blueprint $table) {
            $table->id();
            $table->integer('sort')->default(0); // Поле для хранения порядка сортировки рубрик
            $table->boolean('activity')->default(false); // Активность рубрики
            $table->text('icon')->nullable(); // Иконка рубрики
            $table->string('locale', 2); // Язык (ru, en, kz)
            $table->string('title')->unique(); // Заголовок рубрики
            $table->string('url')->unique(); // Адрес рубрики
            $table->text('short')->nullable(); // Краткое Описание
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
        Schema::dropIfExists('rubrics');
    }
};
