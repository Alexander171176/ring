<?php

namespace Database\Seeders;

use App\Models\Admin\Rubric\Rubric;
use Illuminate\Database\Seeder;

class RubricSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $rubrics = [
            [
                'sort' => 1,
                'activity' => 1,
                'icon' => '<svg class="w-4 h-4 fill-current text-slate-400 shrink-0 mr-3" viewBox="0 0 16 16"><path d="M15 15V5l-5-5H2c-.6 0-1 .4-1 1v14c0 .6.4 1 1 1h12c.6 0 1-.4 1-1zM3 2h6v4h4v8H3V2z"></path></svg>',
                'title' => 'HTML',
                'url' => 'html',
                'short' => 'Изучение основ HTML для создания структурированных веб-страниц.',
                'description' => 'Изучение основ HTML для создания структурированных веб-страниц.',
                'views' => 0,
                'image_url' => '',
                'seo_title' => 'Обучение HTML',
                'seo_alt' => 'HTML для начинающих',
                'meta_title' => 'HTML: основы и структура веб-страниц',
                'meta_keywords' => 'html, веб-разработка, основы html',
                'meta_desc' => 'Учебные материалы по HTML, начиная с основ и заканчивая созданием веб-страниц.',
            ],
            [
                'sort' => 2,
                'activity' => 1,
                'icon' => '<svg class="w-4 h-4 fill-current text-slate-400 shrink-0 mr-3" viewBox="0 0 16 16"><path d="M15 15V5l-5-5H2c-.6 0-1 .4-1 1v14c0 .6.4 1 1 1h12c.6 0 1-.4 1-1zM3 2h6v4h4v8H3V2z"></path></svg>',
                'title' => 'CSS',
                'url' => 'css',
                'short' => 'Изучение CSS для стилизации веб-страниц.',
                'description' => 'Изучение CSS для стилизации веб-страниц.',
                'views' => 0,
                'image_url' => '',
                'seo_title' => 'Обучение CSS',
                'seo_alt' => 'CSS для стилизации веб-страниц',
                'meta_title' => 'CSS: стилизация и дизайн веб-страниц',
                'meta_keywords' => 'css, веб-дизайн, стилизация страниц',
                'meta_desc' => 'Учебные материалы по CSS, охватывающие стилизацию и оформление веб-страниц.',
            ],
            [
                'sort' => 3,
                'activity' => 1,
                'icon' => '<svg class="w-4 h-4 fill-current text-slate-400 shrink-0 mr-3" viewBox="0 0 16 16"><path d="M15 15V5l-5-5H2c-.6 0-1 .4-1 1v14c0 .6.4 1 1 1h12c.6 0 1-.4 1-1zM3 2h6v4h4v8H3V2z"></path></svg>',
                'title' => 'JS',
                'url' => 'js',
                'short' => 'Изучение JavaScript для добавления интерактивности веб-страницам.',
                'description' => 'Изучение JavaScript для добавления интерактивности веб-страницам.',
                'views' => 0,
                'image_url' => '',
                'seo_title' => 'Обучение JavaScript',
                'seo_alt' => 'JavaScript для интерактивных веб-страниц',
                'meta_title' => 'JavaScript: интерактивность на веб-страницах',
                'meta_keywords' => 'javascript, интерактивность, программирование',
                'meta_desc' => 'Учебные материалы по JavaScript для добавления интерактивных функций на веб-страницы.',
            ],
            [
                'sort' => 4,
                'activity' => 1,
                'icon' => '<svg class="w-4 h-4 fill-current text-slate-400 shrink-0 mr-3" viewBox="0 0 16 16"><path d="M15 15V5l-5-5H2c-.6 0-1 .4-1 1v14c0 .6.4 1 1 1h12c.6 0 1-.4 1-1zM3 2h6v4h4v8H3V2z"></path></svg>',
                'title' => 'Laravel',
                'url' => 'laravel',
                'short' => 'Изучение фреймворка Laravel для разработки веб-приложений.',
                'description' => 'Изучение фреймворка Laravel для разработки веб-приложений.',
                'views' => 0,
                'image_url' => '',
                'seo_title' => 'Обучение Laravel',
                'seo_alt' => 'Laravel для разработки веб-приложений',
                'meta_title' => 'Laravel: фреймворк для веб-приложений',
                'meta_keywords' => 'laravel, фреймворк, веб-приложения',
                'meta_desc' => 'Учебные материалы по Laravel для разработки современных веб-приложений.',
            ],
            [
                'sort' => 5,
                'activity' => 1,
                'icon' => '<svg class="w-4 h-4 fill-current text-slate-400 shrink-0 mr-3" viewBox="0 0 16 16"><path d="M15 15V5l-5-5H2c-.6 0-1 .4-1 1v14c0 .6.4 1 1 1h12c.6 0 1-.4 1-1zM3 2h6v4h4v8H3V2z"></path></svg>',
                'title' => 'Vue',
                'url' => 'vue',
                'short' => 'Изучение Vue.js для создания интерактивных пользовательских интерфейсов.',
                'description' => 'Изучение Vue.js для создания интерактивных пользовательских интерфейсов.',
                'views' => 0,
                'image_url' => '',
                'seo_title' => 'Обучение Vue.js',
                'seo_alt' => 'Vue.js для пользовательских интерфейсов',
                'meta_title' => 'Vue.js: создание интерактивных интерфейсов',
                'meta_keywords' => 'vue.js, пользовательские интерфейсы, интерактивность',
                'meta_desc' => 'Учебные материалы по Vue.js для создания гибких и интерактивных интерфейсов.',
            ],
            [
                'sort' => 6,
                'activity' => 1,
                'icon' => '<svg class="w-4 h-4 fill-current text-slate-400 shrink-0 mr-3" viewBox="0 0 16 16"><path d="M15 15V5l-5-5H2c-.6 0-1 .4-1 1v14c0 .6.4 1 1 1h12c.6 0 1-.4 1-1zM3 2h6v4h4v8H3V2z"></path></svg>',
                'title' => 'Tailwind CSS',
                'url' => 'tailwind-css',
                'short' => 'Изучение Tailwind CSS для быстрого создания стилизованных интерфейсов.',
                'description' => 'Изучение Tailwind CSS для быстрого создания стилизованных интерфейсов.',
                'views' => 0,
                'image_url' => '',
                'seo_title' => 'Обучение Tailwind CSS',
                'seo_alt' => 'Tailwind CSS для быстрой стилизации',
                'meta_title' => 'Tailwind CSS: утилитарный фреймворк для стилей',
                'meta_keywords' => 'tailwind css, утилитарные стили, фреймворк css',
                'meta_desc' => 'Учебные материалы по Tailwind CSS для создания адаптивных и стилизованных интерфейсов.',
            ],
        ];

        foreach ($rubrics as $rubric) {
            Rubric::create($rubric);
        }
    }
}
