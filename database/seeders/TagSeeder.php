<?php

namespace Database\Seeders;

use App\Models\Admin\Article\Article;
use App\Models\Admin\Tag\Tag;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TagSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Очистка таблиц перед добавлением новых записей
        DB::table('tags')->truncate();
        DB::table('article_has_tag')->truncate();

        // Список тегов
        $tags = [
            ['name' => 'HTML', 'slug' => 'html', 'locale' => 'ru',],
            ['name' => 'CSS', 'slug' => 'css', 'locale' => 'ru',],
            ['name' => 'JavaScript', 'slug' => 'javascript', 'locale' => 'ru',],
            ['name' => 'Vue.js', 'slug' => 'vuejs', 'locale' => 'ru',],
            ['name' => 'Laravel', 'slug' => 'laravel', 'locale' => 'ru',],
            ['name' => 'Tailwind CSS', 'slug' => 'tailwind-css', 'locale' => 'ru',],
            ['name' => 'Web-разработка', 'slug' => 'web-development', 'locale' => 'ru',],
            ['name' => 'SEO', 'slug' => 'seo', 'locale' => 'ru',],
            ['name' => 'UI/UX', 'slug' => 'ui-ux', 'locale' => 'ru',],
            ['name' => 'Программирование', 'slug' => 'programming', 'locale' => 'ru',],
        ];

        // Создаем теги
        $createdTags = [];
        foreach ($tags as $tag) {
            $createdTags[] = Tag::create($tag);
        }

        // Привязываем теги к статьям
        $articles = Article::all();
        foreach ($articles as $article) {
            // Случайно выбираем 1-3 тега для каждой статьи
            $randomTags = collect($createdTags)->random(rand(1, 3))->pluck('id');
            $article->tags()->sync($randomTags);
        }
    }
}
