<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SectionSeeder extends Seeder
{
    public function run(): void
    {
        $sections = [
            [
                'type' => 'section',
                'tailwind' => 'px-3 py-1',
                'title' => 'История',
                'content' => 'Наша компания была основана в 2010 году группой энтузиастов, которые видели большой потенциал в цифровизации бизнес-процессов.',
                'sort' => 1,
                'activity' => true
            ],
            [
                'type' => 'section',
                'tailwind' => 'px-3 py-1',
                'title' => 'Миссия и ценности',
                'content' => 'Наша миссия — делать технологии доступными и понятными для каждого бизнеса. Мы ценим инновации, открытость и качество.',
                'sort' => 2,
                'activity' => true
            ],
            [
                'type' => 'section',
                'tailwind' => 'px-3 py-1',
                'title' => 'Команда',
                'content' => 'Иван Иванов, CEO; Мария Сидорова, CTO',
                'sort' => 3,
                'activity' => true
            ],
            [
                'type' => 'section',
                'tailwind' => 'px-3 py-1',
                'title' => 'Достижения и награды',
                'content' => 'За 10 лет работы наша компания получила несколько престижных наград в области технологий и инноваций.',
                'sort' => 4,
                'activity' => true
            ],
            [
                'type' => 'section',
                'tailwind' => 'px-3 py-1',
                'title' => 'Контактная информация',
                'content' => 'Адрес: 123456, г. Москва, ул. Пушкина, д. Колотушкина; Телефон: +7 (123) 456-78-90; Email: info@ourcompany.com',
                'sort' => 5,
                'activity' => true
            ]
        ];

        DB::table('sections')->insert($sections);
    }
}
