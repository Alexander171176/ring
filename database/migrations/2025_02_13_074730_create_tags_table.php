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
        Schema::create('tags', function (Blueprint $table) {
            $table->id();
            $table->string('locale', 2); // Язык (ru, en, kz)
            $table->string('name')->unique(); // Название тега
            $table->string('slug')->unique(); // Уникальный URL-friendly идентификатор
            $table->string('short')->nullable(); // Краткое Описание
            $table->text('description')->nullable(); // Описание
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
        Schema::dropIfExists('tags');
    }
};
