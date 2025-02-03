<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $settings = [
            [
                'type' => 'static',
                'option' => 'infoModVersion',
                'value' => '1.0.0',
                'constant' => 'INFO_MOD_VERSION',
                'category' => 'Информ',
                'description' => 'версия движка сайта',
                'activity' => true,
            ],
            [
                'type' => 'string',
                'option' => 'siteLayout',
                'value' => 'Default',
                'constant' => 'SITE_LAYOUT',
                'category' => 'Шаблон сайта',
                'description' => 'Шаблон публичной части сайта',
                'activity' => true,
            ],
            [
                'type' => 'checkbox',
                'option' => 'downtimeSite',
                'value' => 'false',
                'constant' => 'DOWNTIME_SITE',
                'category' => 'Шаблон сайта',
                'description' => 'Включение/Выключение публичной части сайта на технические работы',
                'activity' => true,
            ],
            [
                'type' => 'string',
                'option' => 'widgetHexColor',
                'value' => '155e75',
                'constant' => 'WIDGET_HEX_COLOR',
                'category' => 'Интерфейс панели администратора',
                'description' => 'Задаёт цвет панелей сайдбара и виджетов',
                'activity' => true,
            ],
            [
                'type' => 'float',
                'option' => 'widgetOpacity',
                'value' => '0.99',
                'constant' => 'WIDGET_OPACITY',
                'category' => 'Интерфейс панели администратора',
                'description' => 'Задаёт прозрачность цвета панелей сайдбара и виджетов',
                'activity' => true,
            ],
            [
                'type' => 'string',
                'option' => 'locale',
                'value' => 'ru',
                'constant' => 'LOCALE',
                'category' => 'general',
                'description' => 'переключение локальных языков',
                'activity' => true,
            ],
            // Добавьте остальные параметры
        ];

        DB::table('settings')->insert($settings);
    }
}
