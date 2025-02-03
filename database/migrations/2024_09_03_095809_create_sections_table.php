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
        Schema::create('sections', function (Blueprint $table) {
            $table->id();
            $table->string('type')->default('section');
            $table->text('tailwind')->nullable(); // Добавляем nullable для назначения класса tailwind css
            $table->text('image')->default(''); // Добавляем поле для пути к изображению секции
            $table->string('title')->nullable(); // Делаем поле заголовка необязательным
            $table->text('content')->nullable(); // Делаем поле контента необязательным
            $table->integer('sort')->default(0); // Добавляем поле для порядка отображения
            $table->boolean('activity')->default(false); // Добавляем поле для управления активностью секции
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sections');
    }
};
