<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Запуск миграций.
     * Метод up() вызывается при выполнении команды php artisan migrate.
     */
    public function up(): void
    {
        Schema::create('tournament_types', function (Blueprint $table) {
            $table->id(); // Создает автоинкрементный первичный ключ типа BIGINT с именем 'id'.
            $table->unsignedInteger('sort')->default(0)->index(); // Сортировка Индекс
            $table->boolean('activity')->default(false)->index(); // Активность

            // Создает строковое поле (VARCHAR) с именем 'name'.
            // ->unique() добавляет уникальный индекс на это поле, чтобы значения в нем не повторялись.
            // Пример: "Боксерский поединок", "Турнир MMA", "Пресс-конференция".
            $table->string('name')->unique();

            // Используется для более краткого и подробного описания турнира.
            $table->string('short', 255)->nullable(); // краткое описание
            $table->text('description')->nullable();

            // Создает два столбца временных меток: 'created_at' и 'updated_at'.
            $table->timestamps();
        });
    }

    /**
     * Откат миграций.
     * Метод down() вызывается при выполнении команды php artisan migrate:rollback.
     */
    public function down(): void
    {
        Schema::dropIfExists('tournament_types');
    }
};
