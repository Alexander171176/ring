<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Admin\Rubric\Rubric;
use App\Models\Admin\Rubric\RubricTranslation;

class RubricSeeder extends Seeder
{
    /**
     * Запуск сидера.
     */
    public function run(): void
    {
        $defaultIcon = '<svg class="w-4 h-4 fill-current text-slate-400 shrink-0 mr-3" viewBox="0 0 16 16"><path d="M15 15V5l-5-5H2c-.6 0-1 .4-1 1v14c0 .6 0 1 1 1h12c.6 0 1-.4 1-1zM3 2h6v4h4v8H3V2z"></path></svg>';
        $defaultViews = 0;

        $rubrics = [
            [
                'sort' => 1, 'activity' => 1, 'icon' => $defaultIcon, 'views' => $defaultViews,
                'translations' => [
                    ['locale' => 'en', 'title' => 'News', 'short' => 'Latest news', 'description' => 'Up-to-date news'],
                    ['locale' => 'ru', 'title' => 'Новости', 'short' => 'Свежие новости', 'description' => 'Актуальные новости'],
                    ['locale' => 'kz', 'title' => 'Жаңалықтар', 'short' => 'Жаңа жаңалықтар', 'description' => 'Жаңартылған жаңалықтар'],
                ],
            ],
            [
                'sort' => 2, 'activity' => 1, 'icon' => $defaultIcon, 'views' => $defaultViews,
                'translations' => [
                    ['locale' => 'en', 'title' => 'Schedule', 'short' => 'Upcoming events', 'description' => 'Event schedule'],
                    ['locale' => 'ru', 'title' => 'Расписание', 'short' => 'Ближайшие события', 'description' => 'Расписание мероприятий'],
                    ['locale' => 'kz', 'title' => 'Кесте', 'short' => 'Алдағы оқиғалар', 'description' => 'Іс-шаралар кестесі'],
                ],
            ],
            [
                'sort' => 3, 'activity' => 1, 'icon' => $defaultIcon, 'views' => $defaultViews,
                'translations' => [
                    ['locale' => 'en', 'title' => 'Interview', 'short' => 'Talks with experts', 'description' => 'Interviews with interesting people'],
                    ['locale' => 'ru', 'title' => 'Интервью', 'short' => 'Разговоры с экспертами', 'description' => 'Интервью с интересными людьми'],
                    ['locale' => 'kz', 'title' => 'Сұхбат', 'short' => 'Мамандармен әңгімелер', 'description' => 'Қызықты адамдармен сұхбат'],
                ],
            ],
            [
                'sort' => 4, 'activity' => 1, 'icon' => $defaultIcon, 'views' => $defaultViews,
                'translations' => [
                    ['locale' => 'en', 'title' => 'Reviews', 'short' => 'Analysis and opinions', 'description' => 'Review articles'],
                    ['locale' => 'ru', 'title' => 'Обзоры', 'short' => 'Анализ и мнения', 'description' => 'Обзорные статьи'],
                    ['locale' => 'kz', 'title' => 'Шолулар', 'short' => 'Талдау мен пікірлер', 'description' => 'Шолу мақалалары'],
                ],
            ],
            [
                'sort' => 5, 'activity' => 1, 'icon' => $defaultIcon, 'views' => $defaultViews,
                'translations' => [
                    ['locale' => 'en', 'title' => 'Results', 'short' => 'Event outcomes', 'description' => 'Match and tournament results'],
                    ['locale' => 'ru', 'title' => 'Результаты', 'short' => 'Итоги событий', 'description' => 'Результаты матчей и турниров'],
                    ['locale' => 'kz', 'title' => 'Нәтижелер', 'short' => 'Оқиғалардың нәтижелері', 'description' => 'Матчтар мен турнирлердің нәтижелері'],
                ],
            ],
            [
                'sort' => 6, 'activity' => 1, 'icon' => $defaultIcon, 'views' => $defaultViews,
                'translations' => [
                    ['locale' => 'en', 'title' => 'Open Ring en', 'short' => 'Open fights', 'description' => 'Event information'],
                    ['locale' => 'ru', 'title' => 'Open Ring ru', 'short' => 'Открытые поединки', 'description' => 'Информация о мероприятиях'],
                    ['locale' => 'kz', 'title' => 'Open Ring kz', 'short' => 'Ашық жекпе-жектер', 'description' => 'Іс-шаралар туралы ақпарат'],
                ],
            ],
            [
                'sort' => 7, 'activity' => 1, 'icon' => $defaultIcon, 'views' => $defaultViews,
                'translations' => [
                    ['locale' => 'en', 'title' => 'WBSS en', 'short' => 'World Boxing Super Series', 'description' => 'WBSS news and events'],
                    ['locale' => 'ru', 'title' => 'WBSS ru', 'short' => 'Всемирная бокс-суперсерия', 'description' => 'Новости и события WBSS'],
                    ['locale' => 'kz', 'title' => 'WBSS kz', 'short' => 'Әлемдік бокс суперсериясы', 'description' => 'WBSS жаңалықтары мен оқиғалары'],
                ],
            ],
            [
                'sort' => 8, 'activity' => 1, 'icon' => $defaultIcon, 'views' => $defaultViews,
                'translations' => [
                    ['locale' => 'en', 'title' => 'P4P Rating', 'short' => 'Top fighters', 'description' => 'Ranking of the best P4P fighters'],
                    ['locale' => 'ru', 'title' => 'Рейтинг Р4Р', 'short' => 'Лучшие бойцы', 'description' => 'Рейтинг лучших бойцов P4P'],
                    ['locale' => 'kz', 'title' => 'P4P рейтингі', 'short' => 'Үздік жекпе-жекшілер', 'description' => 'P4P үздік жекпе-жекшілер рейтингі'],
                ],
            ],
        ];

        foreach ($rubrics as $data) {
            $rubric = Rubric::create(collect($data)->except('translations')->toArray());

            foreach ($data['translations'] as $translation) {
                RubricTranslation::create(array_merge($translation, [
                    'rubric_id' => $rubric->id,
                    'url' => $this->transliterate($translation['title']),
                    'meta_title' => $translation['title'],
                    'meta_keywords' => strtolower(str_replace(' ', ',', $translation['title'])),
                    'meta_desc' => $translation['short'],
                ]));
            }
        }
    }

    /**
     * Транслитерация заголовка для формирования URL.
     */
    private function transliterate(string $text): string
    {
        $dictionary = [
            'а' => 'a', 'ә' => 'a', 'б' => 'b', 'в' => 'v', 'г' => 'g', 'ғ' => 'gh', 'д' => 'd',
            'е' => 'e', 'ё' => 'e', 'ж' => 'zh', 'з' => 'z', 'и' => 'i', 'й' => 'y', 'к' => 'k',
            'қ' => 'q', 'л' => 'l', 'м' => 'm', 'н' => 'n', 'ң' => 'ng', 'о' => 'o', 'ө' => 'o',
            'п' => 'p', 'р' => 'r', 'с' => 's', 'т' => 't', 'у' => 'u', 'ұ' => 'u', 'ү' => 'u',
            'ф' => 'f', 'х' => 'kh', 'һ' => 'h', 'ц' => 'ts', 'ч' => 'ch', 'ш' => 'sh', 'щ' => 'shch',
            'ы' => 'y', 'і' => 'i', 'э' => 'e', 'ю' => 'yu', 'я' => 'ya', 'ь' => '', 'ъ' => '', ' ' => '-'
        ];

        // Преобразуем текст в нижний регистр, выполняем транслитерацию
        $transliterated = strtr(mb_strtolower($text), $dictionary);

        // Удаляем все запрещённые символы, кроме латинских букв, цифр и дефиса
        return preg_replace('/[^a-z0-9-]/', '', $transliterated);
    }

}
