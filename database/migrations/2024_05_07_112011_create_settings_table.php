<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('type')->nullable()->index(); // Добавляем индекс
            $table->string('option')->unique(); // Уникальный ключ здесь ОК
            $table->longText('value')->nullable();
            $table->string('constant')->unique(); // Уникальный ключ здесь ОК
            $table->string('category')->nullable()->index(); // Добавляем индекс
            $table->text('description')->nullable();
            $table->boolean('activity')->default(false)->index(); // Добавляем индекс
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
