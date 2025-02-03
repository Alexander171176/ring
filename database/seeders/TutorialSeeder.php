<?php

namespace Database\Seeders;

use App\Models\Admin\Tutorial\Tutorial;
use Faker\Factory as Faker;
use Illuminate\Database\Seeder;

class TutorialSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create();

        // Создаем 16 сущностей
        for ($i = 0; $i < 16; $i++) {
            Tutorial::create([
                'sort' => $faker->numberBetween(1, 100),
                'views' => $faker->numberBetween(1, 100),
                'activity' => $faker->randomElement([0, 1]),
                'icon' => '<svg class="w-4 h-4 fill-current text-slate-400 shrink-0 mr-3" viewBox="0 0 16 16"><path d="M15 15V5l-5-5H2c-.6 0-1 .4-1 1v14c0 .6.4 1 1 1h12c.6 0 1-.4 1-1zM3 2h6v4h4v8H3V2z"></path></svg>',
                'title' => substr($faker->unique()->sentence(3), 0, 255),
                'url' => substr($faker->unique()->slug, 0, 255),
                'short' => substr($faker->text(160), 0, 255),
                'description' => substr($faker->text(160), 0, 255),
                'image_url' => substr($faker->imageUrl(), 0, 255),
                'seo_title' => substr($faker->sentence(10), 0, 255),
                'seo_alt' => substr($faker->sentence(10), 0, 255),
                'meta_title' => substr($faker->sentence(30), 0, 255),
                'meta_keywords' => substr($faker->words(2, true), 0, 255),
                'meta_desc' => substr($faker->text(160), 0, 255),
            ]);
        }
    }
}
