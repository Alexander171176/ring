<?php

namespace Database\Seeders;

use App\Models\Admin\Athlete\Athlete;
use App\Models\Admin\Tournament\Tournament;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TournamentSeeder extends Seeder
{
    public function run(): void
    {
        // Очистка таблицы турниров перед сидированием
        DB::table('tournaments')->truncate();

        $athleteIds = Athlete::pluck('id')->toArray();

        if (count($athleteIds) < 2) {
            $this->command->warn('Недостаточно спортсменов для генерации поединков.');
            return;
        }

        $pairs = collect($athleteIds)->chunk(2)->take(6);

        foreach ($pairs as $index => $pair) {
            $pair = array_values($pair->toArray());

            if (count($pair) !== 2) {
                continue;
            }

            [$redId, $blueId] = $pair;

            $winnerId = rand(0, 2) === 0 ? null : ($index % 2 === 0 ? $redId : $blueId);

            Tournament::create([
                'sort' => $index + 1,
                'activity' => rand(0, 1),
                'locale' => 'ru',
                'name' => "Бой №" . ($index + 1),
                'short' => "Краткое описание боя №" . ($index + 1),
                'description' => "Детальное описание поединка между бойцами $redId и $blueId",
                'tournament_date_time' => now()->subDays(rand(0, 180))->addHours(rand(1, 5)),
                'status' => collect(['scheduled', 'live', 'completed', 'postponed', 'cancelled'])->random(),
                'venue' => 'Спортивный комплекс №' . rand(1, 5),
                'city' => 'Город-' . rand(1, 5),
                'country' => 'Страна-' . rand(1, 3),
                'weight_class_name' => 'Тяжелый вес',
                'rounds_scheduled' => rand(3, 5),
                'is_title_fight' => rand(0, 1),
                'fighter_red_id' => $redId,
                'fighter_blue_id' => $blueId,
                'winner_id' => $winnerId,
                'method_of_victory' => $winnerId ? collect(['KO', 'Submission', 'Decision'])->random() : null,
                'round_of_finish' => $winnerId ? rand(1, 5) : null,
                'time_of_finish' => $winnerId ? '0' . rand(1, 2) . ':' . rand(10, 59) : null,
            ]);
        }
    }

}
