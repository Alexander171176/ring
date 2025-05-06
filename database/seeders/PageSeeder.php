<?php

namespace Database\Seeders;

use App\Models\Admin\Page\Page;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class PageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $locale = 'ru'; // Задаем локаль

        // Опционально: Очистить страницы только для этой локали перед заполнением
        // Это делает сидер идемпотентным (повторный запуск даст тот же результат)
        Page::query()->where('locale', $locale)->delete();

        // Опционально: Сбросить автоинкремент для PostgreSQL, если нужно начинать с ID 1
        // Может вызвать проблемы, если есть другие записи. Используйте осторожно.
        // DB::statement('ALTER SEQUENCE pages_id_seq RESTART WITH 1;');

        $rootPages = [];
        $childrenPages = [];
        $grandChildrenPages = [];

        // 1. Создаем 4 корневые страницы (Level 1)
        for ($i = 1; $i <= 4; $i++) {
            $title = "Главная страница {$i}";
            $rootPages[] = Page::create([
                'parent_id' => null,
                'sort' => $i - 1, // sort = 0, 1, 2, 3
                'activity' => true,
                'locale' => $locale,
                'title' => $title,
                'url' => Str::slug($title), // Генерируем уникальный URL
                'short' => "Краткое описание главной страницы {$i}",
                'description' => "Полное описание главной страницы {$i}",
                'meta_title' => "Мета-заголовок главной {$i}",
                'meta_keywords' => "ключ{$i}, главная",
                'meta_desc' => "Мета-описание главной {$i}",
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // 2. Создаем 4 дочерние страницы (Level 2), по одной для каждой корневой
        foreach ($rootPages as $index => $parent) {
            $i = $index + 1;
            $title = "Дочерняя страница {$i}.1";
            $childrenPages[] = Page::create([
                'parent_id' => $parent->id, // Ссылка на родителя
                'sort' => 0, // Первая (и единственная) дочерняя страница у этого родителя
                'activity' => true,
                'locale' => $locale,
                'title' => $title,
                'url' => Str::slug($title),
                'short' => "Краткое описание дочерней страницы {$i}.1",
                'description' => "Полное описание дочерней страницы {$i}.1",
                'meta_title' => "Мета-заголовок дочерней {$i}.1",
                'meta_keywords' => "ключ{$i}a, дочерняя",
                'meta_desc' => "Мета-описание дочерней {$i}.1",
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // 3. Создаем 4 внучатые страницы (Level 3), по одной для каждой дочерней
        foreach ($childrenPages as $index => $parent) {
            $i = $index + 1;
            $title = "Внучатая страница {$i}.1.1";
            $grandChildrenPages[] = Page::create([ // Сохраняем для порядка, хотя можно и без этого
                'parent_id' => $parent->id, // Ссылка на родителя (дочернюю страницу)
                'sort' => 0, // Первая (и единственная) внучатая страница у этого родителя
                'activity' => true,
                'locale' => $locale,
                'title' => $title,
                'url' => Str::slug($title),
                'short' => "Краткое описание внучатой страницы {$i}.1.1",
                'description' => "Полное описание внучатой страницы {$i}.1.1",
                'meta_title' => "Мета-заголовок внучатой {$i}.1.1",
                'meta_keywords' => "ключ{$i}aa, внучатая",
                'meta_desc' => "Мета-описание внучатой {$i}.1.1",
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Выводим сообщение об успехе в консоль при запуске сидера
        $this->command->info("PageSeeder: Created 12 '{$locale}' pages (4 root, 4 children, 4 grandchildren).");
    }
}
