<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Добавляем все нужные сидеры
        $this->call([
//            RoleSeeder::class,
            RubricSeeder::class,
            ArticleSeeder::class,
            SettingSeeder::class,
            PagesSeeder::class,
            PluginsSeeder::class,
            SectionSeeder::class,
            CommentsSeeder::class,
            ContactsSeeder::class,
            TutorialSeeder::class,
            GuideSeeder::class,
            // Добавьте сюда другие сидеры по мере необходимости
        ]);
    }
}
