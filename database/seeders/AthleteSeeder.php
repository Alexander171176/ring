<?php

namespace Database\Seeders;

use App\Models\Admin\Athlete\Athlete;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class AthleteSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $stances = ['orthodox', 'southpaw', 'switch'];
        $nationalities = ['Казахстан', 'Россия', 'США', 'Бразилия', 'Япония', 'Англия'];

        for ($i = 1; $i <= 12; $i++) {
            Athlete::create([
                'sort' => $i,
                'activity' => rand(0, 1),
                'locale' => 'ru',
                'first_name' => 'Имя' . $i,
                'last_name' => 'Фамилия' . $i,
                'nickname' => 'Прозвище' . $i,
                'date_of_birth' => now()->subYears(rand(20, 35))->subDays(rand(0, 365)),
                'nationality' => $nationalities[array_rand($nationalities)],
                'height_cm' => rand(160, 200),
                'reach_cm' => rand(160, 210),
                'stance' => $stances[array_rand($stances)],
                'bio' => 'Биография спортсмена ' . $i,
                'avatar' => null, // или 'avatars/avatar' . $i . '.jpg', если вы заранее загрузите файлы
                'wins' => rand(5, 20),
                'losses' => rand(0, 5),
                'draws' => rand(0, 2),
                'no_contests' => rand(0, 1),
                'wins_by_ko' => rand(0, 10),
                'wins_by_submission' => rand(0, 5),
                'wins_by_decision' => rand(0, 5),
                'short' => 'Краткое описание спортсмена ' . $i,
                'description' => 'Подробное описание карьеры и достижений спортсмена ' . $i,
            ]);
        }
    }
}
