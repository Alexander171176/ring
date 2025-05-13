<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Запуск миграций.
     */
    public function up(): void
    {
        Schema::create('athletes', function (Blueprint $table) { // Создание таблицы 'athletes' для хранения информации о спортсменах.
            $table->id(); // Первичный ключ 'id', автоинкрементный.
            $table->unsignedInteger('sort')->default(0)->index(); // Сортировка
            $table->boolean('activity')->default(false)->index(); // Активность

            $table->string('first_name'); // Строковое поле для имени спортсмена.
            $table->string('last_name');  // Строковое поле для фамилии спортсмена.
            $table->string('nickname')->nullable(); // Строковое поле для прозвища спортсмена, может быть NULL.
            $table->date('date_of_birth')->nullable(); // Поле для даты рождения, тип DATE, может быть NULL.
            $table->string('nationality')->nullable(); // Строковое поле для национальности, может быть NULL.
            $table->unsignedInteger('height_cm')->nullable(); // Целочисленное беззнаковое поле для роста в сантиметрах, может быть NULL.
            $table->unsignedInteger('reach_cm')->nullable(); // Целочисленное беззнаковое поле для размаха рук в сантиметрах, может быть NULL.

            // Перечисляемый тип (ENUM) для стойки спортсмена.
            // Возможные значения: 'orthodox' (правша), 'southpaw' (левша), 'switch' (сменяемая).
            // Может быть NULL.
            $table->enum('stance', ['orthodox', 'southpaw', 'switch'])->nullable();

            $table->text('bio')->nullable(); // Текстовое поле для биографии спортсмена, может быть NULL.
            $table->string('avatar')->nullable(); // Строковое поле для пути к изображению профиля, может быть NULL.

            // Статистика спортсмена:
            $table->unsignedInteger('wins')->default(0); // Количество побед, по умолчанию 0.
            $table->unsignedInteger('losses')->default(0); // Количество поражений, по умолчанию 0.
            $table->unsignedInteger('draws')->default(0); // Количество ничьих, по умолчанию 0.
            $table->unsignedInteger('no_contests')->default(0); // Количество боев, признанных несостоявшимися, по умолчанию 0.
            $table->unsignedInteger('wins_by_ko')->default(0); // Количество побед нокаутом, по умолчанию 0.
            $table->unsignedInteger('wins_by_submission')->default(0); // Количество побед сдачей (сабмишном), по умолчанию 0.
            $table->unsignedInteger('wins_by_decision')->default(0); // Количество побед решением судей, по умолчанию 0.

            // Используется для более краткого и подробного описания спортсмена.
            $table->string('short', 255)->nullable(); // краткое описание
            $table->text('description')->nullable();

            $table->timestamps(); // Временные метки 'created_at' и 'updated_at'.

            // Добавляет столбец 'deleted_at' для "мягкого удаления".
            // Записи не удаляются физически, а помечаются как удаленные.
            $table->softDeletes();
        });
    }

    /**
     * Откат миграций.
     */
    public function down(): void
    {
        Schema::dropIfExists('athletes'); // Удаление таблицы 'athletes', если она существует.
    }
};
