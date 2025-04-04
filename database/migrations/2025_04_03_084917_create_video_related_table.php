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
        Schema::create('video_related', function (Blueprint $table) {
            $table->unsignedBigInteger('video_id');
            $table->unsignedBigInteger('related_video_id');

            // Добавляем составной первичный ключ
            $table->primary(['video_id', 'related_video_id']);

            // Определяем внешние ключи при необходимости
            $table->foreign('video_id')
                ->references('id')->on('videos')
                ->onDelete('cascade');
            $table->foreign('related_video_id')
                ->references('id')->on('videos')
                ->onDelete('cascade');
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('video_related');
    }
};
