<?php

namespace Database\Seeders;

use App\Models\Admin\Article\Tag;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TagSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tags = [
            ['title' => 'HTML', 'url' => 'html'],
            ['title' => 'CSS', 'url' => 'css'],
            ['title' => 'JavaScript', 'url' => 'javascript'],
            ['title' => 'Vue.js', 'url' => 'vuejs'],
            ['title' => 'Laravel', 'url' => 'laravel'],
            ['title' => 'Tailwind CSS', 'url' => 'tailwind-css'],
            ['title' => 'Web-разработка', 'url' => 'web-development'],
            ['title' => 'SEO', 'url' => 'seo'],
            ['title' => 'UI/UX', 'url' => 'ui-ux'],
            ['title' => 'Программирование', 'url' => 'programming'],
        ];

        foreach ($tags as $tag) {
            Tag::create($tag);
        }
    }
}
