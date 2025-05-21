<?php

namespace Database\Seeders;

use App\Models\Admin\Tournament\Tournament;
use App\Models\Admin\Athlete\Athlete;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class TournamentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $types = ['boxing_bout', 'mma_tournament', 'press_conference'];
        $statuses = ['scheduled', 'live', 'completed', 'postponed', 'cancelled'];
        $countries = ['Казахстан', 'Россия', 'США', 'Бразилия', 'Япония', 'Англия'];
        $venues = ['Almaty Arena', 'MSG', 'Tokyo Dome', 'UFC Apex', 'Wembley'];

        $athletes = Athlete::all();

        for ($i = 1; $i <= 6; $i++) {
            $tournament = Tournament::create([
                'sort' => $i,
                'activity' => rand(0, 1),
                'locale' => 'ru',
                'type' => $types[array_rand($types)],
                'name' => 'Турнир ' . $i,
                'tournament_date_time' => now()->addDays(rand(-10, 30)),
                'status' => $statuses[array_rand($statuses)],
                'venue' => $venues[array_rand($venues)],
                'city' => 'Город ' . $i,
                'country' => $countries[array_rand($countries)],
                'short' => 'Краткое описание турнира ' . $i,
                'description' => 'Подробное описание турнира ' . $i,
                'weight_class_name' => 'Средний вес',
                'rounds_scheduled' => rand(3, 5),
                'is_title_fight' => rand(0, 1),
                'winner_id' => null, // или будет задан позже
                'method_of_victory' => null,
                'round_of_finish' => null,
                'time_of_finish' => null,
                'details' => null,
                'is_main_card_event' => rand(0, 1),
            ]);

            // Назначим 2 случайных спортсменов на бой
            $selectedAthletes = $athletes->random(2);

            $tournament->athletes()->attach($selectedAthletes[0]->id, [
                'corner' => 'red',
                'is_headliner' => true,
                'weight_at_weigh_in_kg' => rand(70, 120),
            ]);

            $tournament->athletes()->attach($selectedAthletes[1]->id, [
                'corner' => 'blue',
                'is_headliner' => false,
                'weight_at_weigh_in_kg' => rand(70, 120),
            ]);
        }
    }
}
