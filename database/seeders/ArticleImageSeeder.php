<?php

namespace Database\Seeders;

use App\Models\Admin\Article\ArticleImage;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ArticleImageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $images = [
            ['path' => 'default-image.png', 'alt' => 'Изображение статьи 1', 'caption' => 'описание изображения',],
            ['path' => 'default-image.png', 'alt' => 'Изображение статьи 2', 'caption' => 'описание изображения',],
            ['path' => 'default-image.png', 'alt' => 'Изображение статьи 3', 'caption' => 'описание изображения',],
            ['path' => 'default-image.png', 'alt' => 'Изображение статьи 4', 'caption' => 'описание изображения',],
            ['path' => 'default-image.png', 'alt' => 'Изображение статьи 5', 'caption' => 'описание изображения',],
            ['path' => 'default-image.png', 'alt' => 'Изображение статьи 6', 'caption' => 'описание изображения',],
            ['path' => 'default-image.png', 'alt' => 'Изображение статьи 7', 'caption' => 'описание изображения',],
            ['path' => 'default-image.png', 'alt' => 'Изображение статьи 8', 'caption' => 'описание изображения',],
            ['path' => 'default-image.png', 'alt' => 'Изображение статьи 9', 'caption' => 'описание изображения',],
        ];

        foreach ($images as $image) {
            ArticleImage::create($image);
        }
    }
}
