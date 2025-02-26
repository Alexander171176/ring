<?php

namespace Database\Seeders;

use App\Models\Admin\Article\Article;
use App\Models\Admin\Article\ArticleImage;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ArticleImageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Очистка таблиц перед запуском сидера
        DB::table('article_has_images')->truncate();
        DB::table('article_images')->truncate();

        // Список изображений
        $images = [
            ['path' => 'default-image.png', 'alt' => 'Изображение статьи 1', 'caption' => 'описание изображения'],
            ['path' => 'default-image.png', 'alt' => 'Изображение статьи 2', 'caption' => 'описание изображения'],
            ['path' => 'default-image.png', 'alt' => 'Изображение статьи 3', 'caption' => 'описание изображения'],
            ['path' => 'default-image.png', 'alt' => 'Изображение статьи 4', 'caption' => 'описание изображения'],
            ['path' => 'default-image.png', 'alt' => 'Изображение статьи 5', 'caption' => 'описание изображения'],
            ['path' => 'default-image.png', 'alt' => 'Изображение статьи 6', 'caption' => 'описание изображения'],
            ['path' => 'default-image.png', 'alt' => 'Изображение статьи 7', 'caption' => 'описание изображения'],
            ['path' => 'default-image.png', 'alt' => 'Изображение статьи 8', 'caption' => 'описание изображения'],
            ['path' => 'default-image.png', 'alt' => 'Изображение статьи 9', 'caption' => 'описание изображения'],
        ];

        // Создание изображений и привязка к статьям
        foreach ($images as $imageData) {
            $image = ArticleImage::create($imageData);

            // Случайная привязка к статьям
            $articleIds = Article::inRandomOrder()->limit(rand(1, 3))->pluck('id');
            $image->articles()->sync($articleIds);
        }
    }
}
