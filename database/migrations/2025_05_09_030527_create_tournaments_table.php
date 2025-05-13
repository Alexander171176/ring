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
        Schema::create('tournaments', function (Blueprint $table) { // Создание таблицы 'tournaments' для хранения информации о турнирах/поединках.
            $table->id(); // Первичный ключ 'id', автоинкрементный.
            $table->unsignedInteger('sort')->default(0)->index(); // Сортировка Индекс
            $table->boolean('activity')->default(false)->index(); // Активность

            // Внешний ключ для связи с родительским турниром (для структуры "турнир -> кард боев").
            // Ссылается на поле 'id' в этой же таблице 'tournaments'.
            $table->foreignId('parent_tournament_id')
                ->nullable() // Может быть NULL, если это турнир верхнего уровня или одиночный поединок.
                ->constrained('tournaments') // Указывает, что это внешний ключ на таблицу 'tournaments'.
                ->onDelete('cascade'); // При удалении родительского турнира, все дочерние (карды, бои) также будут удалены.

            // Внешний ключ для связи с типом турнира/события.
            $table->foreignId('tournament_type_id')
                ->constrained('tournament_types') // Указывает, что это внешний ключ на таблицу 'tournament_types'.
                ->onDelete('cascade'); // При удалении типа, связанные турниры также удаляются (или onDelete('restrict') чтобы запретить удаление типа, если есть связанные турниры).

            $table->string('name'); // Название турнира или поединка (например, "UFC 300", "Main Card", "Волков vs. Павлович").
            $table->dateTime('tournament_date_time'); // Дата и время проведения турнира/поединка.
            $table->enum('status', ['scheduled', 'live', 'completed', 'postponed', 'cancelled']) // Статус турнира/поединка.
            ->default('scheduled'); // По умолчанию 'scheduled' (запланирован).

            $table->string('venue')->nullable(); // Место проведения (например, "Madison Square Garden"), может быть NULL.
            $table->string('city')->nullable(); // Город проведения, может быть NULL.
            $table->string('country')->nullable(); // Страна проведения, может быть NULL.

            $table->string('short', 255)->nullable(); // Краткое описание турнира, может быть NULL.
            $table->text('description')->nullable(); // Общее описание турнира, может быть NULL.

            // Характеристики, специфичные для поединка (будут NULL для событий-контейнеров/турниров):
            $table->string('weight_class_name')->nullable(); // Название весовой категории текстом (например, "Тяжелый вес"), может быть NULL.
            $table->unsignedTinyInteger('rounds_scheduled')->nullable(); // Количество запланированных раундов, может быть NULL.
            $table->boolean('is_title_fight')->nullable()->default(false); // Является ли поединок титульным, по умолчанию false, может быть NULL.

            // Внешний ключ для указания победителя поединка.
            $table->foreignId('winner_id')
                ->nullable() // Может быть NULL (например, если бой еще не состоялся или завершился ничьей/не состоялся).
                ->constrained('athletes') // Ссылка на таблицу 'athletes'.
                ->onDelete('set null'); // Если спортсмен-победитель удаляется, в этом поле будет установлено NULL.

            $table->string('method_of_victory')->nullable(); // Метод победы (например, "KO", "Submission"), может быть NULL.
            $table->unsignedTinyInteger('round_of_finish')->nullable(); // Раунд, в котором завершился поединок, может быть NULL.
            $table->string('time_of_finish')->nullable(); // Время в раунде завершения поединка (например, "02:35"), может быть NULL.

            $table->text('details')->nullable(); // Дополнительные детали/комментарии к турниру или поединку, может быть NULL.
            // Флаг, указывающий, является ли данная запись "картой" боев (например, главный кард, прелимы)
            // в рамках более крупного турнира.
            $table->boolean('is_main_card_event')->default(false);

            $table->timestamps(); // Временные метки 'created_at' и 'updated_at'.
            $table->softDeletes(); // "Мягкое удаление".

            // Индексы для ускорения выборок по часто используемым полям:
            $table->index('tournament_date_time'); // Индекс на дату и время турнира.
            $table->index('status'); // Индекс на статус турнира.
            // Композитный индекс на parent_tournament_id и tournament_type_id.
            $table->index(['parent_tournament_id', 'tournament_type_id']);
        });
    }

    /**
     * Откат миграций.
     */
    public function down(): void
    {
        Schema::dropIfExists('tournaments'); // Удаление таблицы 'tournaments', если она существует.
    }
};
