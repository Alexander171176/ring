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
        // Создание пивотной (промежуточной) таблицы 'athlete_has_tournament'
        // для реализации связи "многие ко многим" между таблицами 'athletes' и 'tournaments'.
        // Один спортсмен может участвовать во многих турнирах/поединках,
        // и в одном турнире/поединке (в случае командных или специфических форматов) может быть много спортсменов,
        // но обычно в поединке два спортсмена.
        Schema::create('athlete_has_tournament', function (Blueprint $table) {
            $table->id(); // Первичный ключ для записи в пивотной таблице.

            // Внешний ключ, ссылающийся на 'id' в таблице 'athletes'.
            $table->foreignId('athlete_id')
                ->constrained() // Автоматически определяет таблицу 'athletes' по имени поля 'athlete_id'.
                ->onDelete('cascade'); // При удалении спортсмена, его участие во всех турнирах также удаляется.

            // Внешний ключ, ссылающийся на 'id' в таблице 'tournaments'.
            $table->foreignId('tournament_id')
                ->constrained('tournaments') // Явно указываем таблицу 'tournaments'.
                ->onDelete('cascade'); // При удалении турнира, все записи об участии спортсменов в нем удаляются.

            // Дополнительные поля, специфичные для связи спортсмена и конкретного турнира/поединка:
            $table->string('corner')->nullable(); // Угол спортсмена в поединке (например, "красный", "синий"), может быть NULL.
            // Является ли спортсмен участником главного боя данного турнира/карты.
            // ->comment() добавляет комментарий к полю в некоторых СУБД (например, MySQL).
            $table->boolean('is_headliner')->default(false)->comment('Является ли участником главного боя карты/турнира');
            // Вес спортсмена на официальном взвешивании перед поединком, в килограммах.
            // decimal(5, 2) означает число с 5 знаками всего, из которых 2 после запятой (например, 70.55).
            $table->decimal('weight_at_weigh_in_kg', 5, 2)->nullable()->comment('Вес на официальном взвешивании в кг');

            $table->timestamps(); // Временные метки 'created_at' и 'updated_at' для записи в пивотной таблице.

            // Уникальный составной индекс на 'athlete_id' и 'tournament_id'.
            // Гарантирует, что один и тот же спортсмен не может быть привязан к одному и тому же турниру/поединку дважды.
            $table->unique(['athlete_id', 'tournament_id']);
        });
    }

    /**
     * Откат миграций.
     */
    public function down(): void
    {
        Schema::dropIfExists('athlete_has_tournament'); // Удаление пивотной таблицы 'athlete_has_tournament', если она существует.
    }
};
