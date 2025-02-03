<?php

namespace Database\Seeders;

use App\Models\Admin\Guide\Guide;
use App\Models\Admin\Tutorial\Tutorial;
use Faker\Factory as Faker;
use Illuminate\Database\Seeder;

class GuideSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create();

        // Получаем все рубрики
        $tutorials = Tutorial::all();

        for ($i = 0; $i < 48; $i++) {
            // Создаем статью
            $guide = Guide::create([
                'sort' => $faker->numberBetween(1, 100),
                'activity' => $faker->randomElement([0, 1]),
                'title' => $faker->unique()->sentence(3, true),
                'url' => substr($faker->unique()->slug, 0, 255),
                'short' => substr($faker->text(160), 0, 255),
                'description' => substr($faker->text(160), 0, 255),
                'author' => substr($faker->name, 0, 255),
                'tags' => substr($faker->words(3, true), 0, 255),
                'views' => $faker->numberBetween(1, 100),
                'likes' => $faker->numberBetween(1, 100),
                'image_url' => substr($faker->imageUrl(), 0, 255),
                'seo_title' => substr($faker->sentence(6, true), 0, 255),
                'seo_alt' => substr($faker->sentence(6, true), 0, 255),
                'meta_title' => substr($faker->sentence(6, true), 0, 255),
                'meta_keywords' => substr($faker->words(5, true), 0, 255),
                'meta_desc' => substr($faker->paragraph(3, true), 0, 255),
            ]);

            $tutorial = $tutorials->random();
            $guide->tutorials()->attach($tutorial->id);
        }

    }
}
