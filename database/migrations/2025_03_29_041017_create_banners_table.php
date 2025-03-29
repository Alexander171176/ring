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
        Schema::create('banners', function (Blueprint $table) {
            $table->id();
            $table->integer('sort')->default(0); // Поле для хранения порядка сортировки постов
            $table->boolean('activity')->default(false); // Активность поста
            $table->boolean('left')->default(false); // Печатать пост в левом сайдбаре
            $table->boolean('right')->default(false); // Печатать в правом сайдбаре
            $table->string('title')->unique(); // Заголовок
            $table->string('short')->nullable(); // Краткое Описание
            $table->string('comment')->nullable(); // Комментарий
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('banners');
    }
};
