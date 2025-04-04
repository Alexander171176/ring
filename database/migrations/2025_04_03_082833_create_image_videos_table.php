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
        Schema::create('image_videos', function (Blueprint $table) {
            $table->id();
            $table->integer('order')->default(0); // Поле для хранения порядка сортировки постов
            $table->string('path')->nullable(); // Резервное поле
            $table->string('alt')->nullable(); // Альтернативный текст
            $table->string('caption')->nullable(); // Подпись к изображению
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('image_videos');
    }
};
