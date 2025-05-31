<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tournaments', function (Blueprint $table) {
            $table->id(); // ID поединка
            $table->unsignedInteger('sort')->default(0)->index(); // Сортировка
            $table->boolean('activity')->default(false)->index(); // Флаг активности
            $table->string('locale', 2)->index(); // Язык

            $table->string('name'); // Название боя (например, "Фёдоров vs Иванов")
            $table->string('short', 255)->nullable(); // краткое описание
            $table->text('description')->nullable(); // описание

            $table->dateTime('tournament_date_time'); // Дата и время поединка
            $table->enum('status', ['scheduled', 'live', 'completed', 'postponed', 'cancelled'])
                ->default('scheduled')
                ->index();

            $table->string('venue')->nullable();   // Арена
            $table->string('city')->nullable();    // Город
            $table->string('country')->nullable(); // Страна

            $table->string('weight_class_name')->nullable();        // Весовая категория
            $table->unsignedTinyInteger('rounds_scheduled')->nullable(); // Запланированное количество раундов
            $table->boolean('is_title_fight')->default(false);      // Титульный ли бой

            // Связи с бойцами
            $table->foreignId('fighter_red_id')->nullable()
                ->constrained('athletes')->onDelete('set null');

            $table->foreignId('fighter_blue_id')->nullable()
                ->constrained('athletes')->onDelete('set null');

            $table->foreignId('winner_id')->nullable()
                ->constrained('athletes')->onDelete('set null');

            $table->string('method_of_victory')->nullable();     // Метод победы (KO, Submission, Decision и т.п.)
            $table->unsignedTinyInteger('round_of_finish')->nullable(); // Раунд завершения
            $table->string('time_of_finish')->nullable();        // Время в раунде (например, 02:35)

            $table->timestamps();     // created_at, updated_at
            $table->softDeletes();    // deleted_at

            $table->unique(['locale', 'name']); // Уникальность на имя и локаль
            $table->index('tournament_date_time'); // Индексация по дате
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tournaments');
    }
};
