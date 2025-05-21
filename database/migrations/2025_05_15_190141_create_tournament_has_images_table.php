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
        Schema::create('tournament_has_images', function (Blueprint $table) {
            // Внешний ключ на таблицу athletes
            $table->foreignId('tournament_id')->constrained('tournaments')->onDelete('cascade');
            // Внешний ключ на таблицу tournament_images (где хранятся метаданные и ссылка на файл Spatie)
            $table->foreignId('image_id')->constrained('tournament_images')->onDelete('cascade');
            // Поле для сортировки изображений конкретной сущности
            $table->unsignedInteger('order')->default(0);

            // Составной первичный ключ
            $table->primary(['tournament_id', 'image_id']);
            // Индекс на order для сортировки внутри атлета
            $table->index('order');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tournament_has_images');
    }
};
