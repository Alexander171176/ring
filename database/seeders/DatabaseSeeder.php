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
            PluginsSeeder::class,
            CommentsSeeder::class,
            // Добавьте сюда другие сидеры по мере необходимости
        ]);
    }
}
