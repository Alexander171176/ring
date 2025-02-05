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
            $table->unsignedBigInteger('views')->default(0); // Количество просмотров
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
