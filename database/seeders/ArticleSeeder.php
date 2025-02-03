<?php

namespace Database\Seeders;

use App\Models\Admin\Article\Article;
use App\Models\Admin\Rubric\Rubric;
use Illuminate\Database\Seeder;

class ArticleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Получаем все рубрики
        $rubrics = Rubric::whereIn('title', ['HTML', 'CSS', 'JS', 'Laravel', 'Vue', 'Tailwind CSS'])->get();

        $articles = [
            // HTML Articles
            [
                'sort' => 1,
                'activity' => 1,
                'title' => 'Основы HTML: Как начать?',
                'url' => 'osnovy-html-kak-nachat',
                'short' => 'Первый шаг к освоению HTML: базовые теги и их применение.',
                'description' => 'Статья объясняет, как начать работать с HTML, что такое теги и как с ними взаимодействовать для создания базовых структур на веб-страницах.',
                'author' => 'Иван Иванов',
                'tags' => 'html, основы, теги',
                'views' => 0,
                'likes' => 0,
                'image_url' => '',
                'seo_title' => 'Основы HTML: с чего начать',
                'seo_alt' => 'Основы HTML для новичков',
                'meta_title' => 'HTML для начинающих: базовые шаги',
                'meta_keywords' => 'html, основы, теги',
                'meta_desc' => 'Начальный путь в HTML, основные теги и их использование для создания веб-страниц.',
            ],
            [
                'sort' => 2,
                'activity' => 1,
                'title' => 'Семантические теги в HTML',
                'url' => 'semanticheskie-tegi-v-html',
                'short' => 'Значение семантических тегов для правильной структуры веб-страниц.',
                'description' => 'Обзор семантических тегов HTML, таких как <header>, <article>, <footer>, и их значение в SEO и доступности сайтов.',
                'author' => 'Мария Федорова',
                'tags' => 'html, семантика, seo',
                'views' => 0,
                'likes' => 0,
                'image_url' => '',
                'seo_title' => 'Семантика в HTML для улучшения структуры страниц',
                'seo_alt' => 'Семантические теги в HTML',
                'meta_title' => 'HTML семантика: как улучшить SEO и структуру',
                'meta_keywords' => 'html, семантика, структура',
                'meta_desc' => 'Изучение семантических тегов HTML и их влияние на SEO и доступность сайтов.',
            ],

            // CSS Articles
            [
                'sort' => 3,
                'activity' => 1,
                'title' => 'Основы CSS: Как стилизовать страницы',
                'url' => 'osnovy-css-kak-stilizovat-stranicy',
                'short' => 'Основные правила и синтаксис CSS для создания стилей на веб-страницах.',
                'description' => 'Статья раскрывает базовые концепции CSS: как применять стили к элементам HTML, основные свойства и селекторы.',
                'author' => 'Алексей Смирнов',
                'tags' => 'css, стили, основы',
                'views' => 0,
                'likes' => 0,
                'image_url' => '',
                'seo_title' => 'Основы CSS: стилизация для новичков',
                'seo_alt' => 'CSS стилизация веб-страниц',
                'meta_title' => 'CSS основы: как начать стилизовать веб-страницы',
                'meta_keywords' => 'css, стили, селекторы',
                'meta_desc' => 'Основы CSS и как использовать стили для улучшения внешнего вида веб-страниц.',
            ],
            [
                'sort' => 4,
                'activity' => 1,
                'title' => 'Flexbox: Секреты гибкой верстки на CSS',
                'url' => 'flexbox-sekrety-gibkoi-verstki',
                'short' => 'Изучаем возможности Flexbox для создания гибких и адаптивных макетов.',
                'description' => 'Полное руководство по Flexbox: от основ до продвинутых приемов верстки. Как управлять распределением пространства и выравниванием элементов.',
                'author' => 'Иван Иванов',
                'tags' => 'css, flexbox, верстка',
                'views' => 0,
                'likes' => 0,
                'image_url' => '',
                'seo_title' => 'Flexbox: мощный инструмент для адаптивных макетов',
                'seo_alt' => 'Flexbox CSS',
                'meta_title' => 'Flexbox в CSS: как создавать адаптивные макеты',
                'meta_keywords' => 'css, flexbox, верстка',
                'meta_desc' => 'Гид по Flexbox, который поможет создавать гибкие и адаптивные макеты на CSS.',
            ],

            // JavaScript Articles
            [
                'sort' => 5,
                'activity' => 1,
                'title' => 'Основы JavaScript: Переменные и функции',
                'url' => 'osnovy-javascript-peremennye-i-funktsii',
                'short' => 'Изучаем переменные, типы данных и функции в JavaScript.',
                'description' => 'Статья объясняет ключевые концепции JavaScript: переменные, их типы и как работать с функциями для добавления интерактивности на сайты.',
                'author' => 'Мария Федорова',
                'tags' => 'javascript, переменные, функции',
                'views' => 0,
                'likes' => 0,
                'image_url' => '',
                'seo_title' => 'Основы JavaScript: работа с переменными и функциями',
                'seo_alt' => 'JavaScript переменные и функции',
                'meta_title' => 'JavaScript основы: как работать с переменными и функциями',
                'meta_keywords' => 'javascript, переменные, функции',
                'meta_desc' => 'Погружение в базовые аспекты JavaScript: как использовать переменные и функции.',
            ],
            [
                'sort' => 6,
                'activity' => 1,
                'title' => 'События и манипуляции DOM в JavaScript',
                'url' => 'sobytia-i-manipulyatsii-dom-v-javascript',
                'short' => 'Как работать с событиями и изменять DOM структуру с помощью JavaScript.',
                'description' => 'Рассматриваются основные события JavaScript и способы изменения DOM элементов для динамического взаимодействия с пользователем.',
                'author' => 'Иван Иванов',
                'tags' => 'javascript, события, dom',
                'views' => 0,
                'likes' => 0,
                'image_url' => '',
                'seo_title' => 'События в JavaScript и работа с DOM',
                'seo_alt' => 'JavaScript события и DOM',
                'meta_title' => 'JavaScript события: как манипулировать DOM',
                'meta_keywords' => 'javascript, события, dom',
                'meta_desc' => 'Изучение событий JavaScript и способов взаимодействия с DOM для динамических страниц.',
            ],

            // Laravel Articles
            [
                'sort' => 7,
                'activity' => 1,
                'title' => 'Основы Laravel: Маршрутизация и контроллеры',
                'url' => 'osnovy-laravel-marshrutizatsiya-i-kontrollery',
                'short' => 'Как настроить маршрутизацию и контроллеры в Laravel.',
                'description' => 'Эта статья поможет разобраться с основными принципами маршрутизации и созданием контроллеров в Laravel.',
                'author' => 'Алексей Смирнов',
                'tags' => 'laravel, маршруты, контроллеры',
                'views' => 0,
                'likes' => 0,
                'image_url' => '',
                'seo_title' => 'Маршруты и контроллеры в Laravel',
                'seo_alt' => 'Laravel маршрутизация',
                'meta_title' => 'Laravel маршруты: настройка и работа с контроллерами',
                'meta_keywords' => 'laravel, маршруты, контроллеры',
                'meta_desc' => 'Основы работы с маршрутами и контроллерами в Laravel для создания веб-приложений.',
            ],
            [
                'sort' => 8,
                'activity' => 1,
                'title' => 'Работа с базой данных в Laravel: миграции и модели',
                'url' => 'rabota-s-bazoi-dannykh-v-laravel-migratsii-i-modeli',
                'short' => 'Как создавать миграции и работать с моделями в Laravel.',
                'description' => 'Статья рассматривает основные аспекты работы с базой данных в Laravel, включая создание миграций и моделей для управления данными.',
                'author' => 'Мария Федорова',
                'tags' => 'laravel, базы данных, миграции',
                'views' => 0,
                'likes' => 0,
                'image_url' => '',
                'seo_title' => 'Работа с базой данных в Laravel: миграции и модели',
                'seo_alt' => 'Laravel миграции и модели',
                'meta_title' => 'Laravel базы данных: миграции и работа с моделями',
                'meta_keywords' => 'laravel, базы данных, миграции',
                'meta_desc' => 'Руководство по созданию и управлению базами данных в Laravel через миграции и модели.',
            ],

            // Vue.js Articles
            [
                'sort' => 9,
                'activity' => 1,
                'title' => 'Основы Vue.js: создание компонентов',
                'url' => 'osnovy-vuejs-sozdanie-komponentov',
                'short' => 'Как начать работу с Vue.js и создавать компоненты для веб-интерфейсов.',
                'description' => 'Погружение в основы Vue.js, создание и использование компонентов для построения интерфейсов.',
                'author' => 'Иван Иванов',
                'tags' => 'vue.js, компоненты, основы',
                'views' => 0,
                'likes' => 0,
                'image_url' => '',
                'seo_title' => 'Создание компонентов во Vue.js',
                'seo_alt' => 'Vue.js компоненты',
                'meta_title' => 'Основы Vue.js: как создавать компоненты',
                'meta_keywords' => 'vue.js, компоненты, веб-интерфейсы',
                'meta_desc' => 'Руководство по началу работы с Vue.js и созданию компонентов для построения веб-интерфейсов.',
            ],
            [
                'sort' => 10,
                'activity' => 1,
                'title' => 'Двустороннее связывание данных во Vue.js',
                'url' => 'dvustoronnee-svyazyvanie-dannykh-vuejs',
                'short' => 'Как работает двустороннее связывание данных в Vue.js и его преимущества.',
                'description' => 'Рассмотрение концепции двустороннего связывания данных во Vue.js и как его можно использовать для создания реактивных приложений.',
                'author' => 'Мария Федорова',
                'tags' => 'vue.js, связывание данных, реактивность',
                'views' => 0,
                'likes' => 0,
                'image_url' => '',
                'seo_title' => 'Двустороннее связывание данных в Vue.js',
                'seo_alt' => 'Vue.js двустороннее связывание',
                'meta_title' => 'Vue.js: как работает двустороннее связывание данных',
                'meta_keywords' => 'vue.js, связывание данных, реактивность',
                'meta_desc' => 'Изучение двустороннего связывания данных во Vue.js и его роли в создании реактивных интерфейсов.',
            ],

            // Tailwind CSS Articles
            [
                'sort' => 11,
                'activity' => 1,
                'title' => 'Основы Tailwind CSS: утилитарные классы для быстрого дизайна',
                'url' => 'osnovy-tailwind-css-utilitarnye-klassy',
                'short' => 'Как использовать утилитарные классы Tailwind CSS для создания адаптивных интерфейсов.',
                'description' => 'Рассмотрение основ работы с Tailwind CSS, как быстро и эффективно применять стили с помощью утилитарных классов.',
                'author' => 'Алексей Смирнов',
                'tags' => 'tailwind css, утилиты, адаптивный дизайн',
                'views' => 0,
                'likes' => 0,
                'image_url' => '',
                'seo_title' => 'Tailwind CSS: как применять утилитарные классы для быстрого дизайна',
                'seo_alt' => 'Tailwind CSS утилитарные классы',
                'meta_title' => 'Tailwind CSS основы: использование утилитарных классов',
                'meta_keywords' => 'tailwind css, утилиты, дизайн',
                'meta_desc' => 'Изучение работы с Tailwind CSS для быстрого создания адаптивных и стилизованных интерфейсов с помощью утилитарных классов.',
            ],
            [
                'sort' => 12,
                'activity' => 1,
                'title' => 'Создание адаптивного дизайна с Tailwind CSS',
                'url' => 'sozdanie-adaptivnogo-dizaina-tailwind-css',
                'short' => 'Как использовать Tailwind CSS для создания адаптивных интерфейсов.',
                'description' => 'Гид по созданию адаптивных интерфейсов с помощью Tailwind CSS и правильного применения классов для различных разрешений экранов.',
                'author' => 'Иван Иванов',
                'tags' => 'tailwind css, адаптивность, дизайн',
                'views' => 0,
                'likes' => 0,
                'image_url' => '',
                'seo_title' => 'Адаптивный дизайн с Tailwind CSS',
                'seo_alt' => 'Tailwind CSS адаптивность',
                'meta_title' => 'Tailwind CSS: создание адаптивного дизайна',
                'meta_keywords' => 'tailwind css, адаптивность, веб-дизайн',
                'meta_desc' => 'Подробное руководство по созданию адаптивного дизайна интерфейсов с помощью Tailwind CSS.',
            ],
        ];

        // Создаем статьи и связываем их с рубриками
        foreach ($articles as $articleData) {
            $article = Article::create($articleData);
            $rubric = $rubrics->random();
            $article->rubrics()->attach($rubric->id);
        }
    }
}
