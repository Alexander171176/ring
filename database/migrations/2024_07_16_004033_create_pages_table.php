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
        Schema::create('pages', function (Blueprint $table) {
            $table->id();
            $table->integer('sort')->default(0); // Поле для хранения порядка сортировки
            $table->string('title')->unique(); // Заголовок
            $table->string('url')->unique(); // Адрес
            $table->string('slug')->unique(); // Адрес
            $table->text('content'); // Контент
            $table->string('meta_title')->nullable(); // meta title
            $table->string('meta_keywords')->nullable(); // meta keywords
            $table->text('meta_desc')->nullable(); // meta description
            $table->boolean('activity')->default(true); // Активность
            $table->boolean('print_in_menu')->default(true); // Показывать в меню
            $table->boolean('without_style')->default(true); // применять стили шаблона
            $table->unsignedBigInteger('parent_id')->nullable()->default(null); // Родительская категория
            $table->foreign('parent_id')->references('id')->on('pages')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pages');
    }
};
