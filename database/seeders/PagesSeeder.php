<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PagesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('pages')->insert([
            [
                'sort' => 1,
                'title' => 'abouts',
                'url' => 'abouts',
                'slug' => 'abouts',
                'content' => '<h1>About Us</h1><p>Эта страница О Нас</p>',
                'meta_title' => 'About Us',
                'meta_keywords' => 'about, us, information',
                'meta_desc' => 'Learn more about us',
                'activity' => true,
                'print_in_menu' => true,
                'without_style' => false,
                'parent_id' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'sort' => 2,
                'title' => 'blog',
                'url' => 'blog',
                'slug' => 'blog',
                'content' => '<h1>Our Blog</h1><p>Эта страница Блог.</p>',
                'meta_title' => 'Blog',
                'meta_keywords' => 'blog, posts',
                'meta_desc' => 'Our latest blog posts',
                'activity' => true,
                'print_in_menu' => true,
                'without_style' => false,
                'parent_id' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'sort' => 3,
                'title' => 'services',
                'url' => 'services',
                'slug' => 'services',
                'content' => '<h1>Our Services</h1><p>Эта страница Сервис.</p>',
                'meta_title' => 'Services',
                'meta_keywords' => 'services, details',
                'meta_desc' => 'Overview of our services',
                'activity' => true,
                'print_in_menu' => true,
                'without_style' => false,
                'parent_id' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'sort' => 4,
                'title' => 'contacts',
                'url' => 'contacts',
                'slug' => 'contacts',
                'content' => '<h1>Contact Us</h1><p>Эта страница Контакты.</p>',
                'meta_title' => 'Contacts',
                'meta_keywords' => 'contacts, get in touch',
                'meta_desc' => 'How to contact us',
                'activity' => true,
                'print_in_menu' => true,
                'without_style' => false,
                'parent_id' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'sort' => 5,
                'title' => 'feedback',
                'url' => 'feedback',
                'slug' => 'feedback',
                'content' => '<h1>Feedback</h1><p>Эта страница Форма обратной связи.</p>',
                'meta_title' => 'feedback',
                'meta_keywords' => 'feedback, get in touch',
                'meta_desc' => 'How to contact us',
                'activity' => true,
                'print_in_menu' => true,
                'without_style' => false,
                'parent_id' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'sort' => 6,
                'title' => 'tutorials',
                'url' => 'tutorials',
                'slug' => 'tutorials',
                'content' => '<h1>Tutorials</h1><p>Эта страница Категорий Руководств.</p>',
                'meta_title' => 'tutorials',
                'meta_keywords' => 'tutorials, get in touch',
                'meta_desc' => 'How to contact us',
                'activity' => true,
                'print_in_menu' => true,
                'without_style' => false,
                'parent_id' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'sort' => 7,
                'title' => 'guides',
                'url' => 'guides',
                'slug' => 'guides',
                'content' => '<h1>Guides</h1><p>Эта страница Инструкций.</p>',
                'meta_title' => 'guides',
                'meta_keywords' => 'guides, get in touch',
                'meta_desc' => 'How to contact us',
                'activity' => true,
                'print_in_menu' => true,
                'without_style' => false,
                'parent_id' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
