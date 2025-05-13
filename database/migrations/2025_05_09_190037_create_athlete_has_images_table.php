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
        Schema::create('athlete_has_images', function (Blueprint $table) {
            // Внешний ключ на таблицу athletes
            $table->foreignId('athlete_id')->constrained('athletes')->onDelete('cascade');
            // Внешний ключ на таблицу athlete_images (где хранятся метаданные и ссылка на файл Spatie)
            $table->foreignId('image_id')->constrained('athlete_images')->onDelete('cascade');
            // Поле для сортировки изображений конкретной сущности
            $table->unsignedInteger('order')->default(0);

            // Составной первичный ключ
            $table->primary(['athlete_id', 'image_id']);
            // Индекс на order для сортировки внутри атлета
            $table->index('order');

            // Пивотным таблицам обычно не нужны timestamps, если только вы не отслеживаете,
            // когда именно была установлена связь. Если они не нужны, их можно убрать.
            // $table->timestamps(); // Если нужны, добавьте.
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('athlete_has_images');
    }
};
