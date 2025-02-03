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
        Schema::create('contacts', function (Blueprint $table) {
            $table->id();
            $table->text('image')->default(''); // Добавляем поле для пути к изображению
            $table->text('tailwind')->nullable(); // Добавляем nullable для назначения класса tailwind css
            $table->string('title')->nullable(); // Делаем поле заголовка необязательным
            $table->text('content')->nullable(); // Делаем поле контента необязательным
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contacts');
    }
};
