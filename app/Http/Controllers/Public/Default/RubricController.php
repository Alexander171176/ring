<?php

namespace App\Http\Controllers\Public\Default;

use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\Article\ArticleResource;
use App\Http\Resources\Admin\Rubric\RubricResource;
use App\Http\Resources\Admin\Section\SectionResource;
use App\Models\Admin\Article\Article;
use App\Models\Admin\Rubric\Rubric;
use App\Models\Admin\Setting\Setting;
use Illuminate\Http\JsonResponse;
use Inertia\Inertia;
use Inertia\Response;

class RubricController extends Controller
{
    /**
     * Возвращает список активных рубрик в зависимости от выбранного языка.
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        // Получаем текущую локаль
        $locale = Setting::where('option', 'locale')->value('value');

        // Логирование для проверки текущей локали
        //Log::info("Текущая локаль для фильтрации: " . $locale);

        // Получаем рубрики с фильтрацией по активности и локали
        $rubrics = Rubric::where('activity', 1)
            ->where('locale', $locale)
            ->orderBy('sort')
            ->get(['id', 'title', 'url', 'locale']);

        // Логирование результата перед отправкой
        //Log::info("Найденные рубрики: ", $rubrics->toArray());

        return response()->json([
            'rubrics' => $rubrics,
            'rubricsCount' => $rubrics->count()
        ]);
    }

    /*
     * Страница показа рубрики
     */
    public function show(string $url): Response
    {
        // Получаем текущую локаль из настроек
        $locale = Setting::where('option', 'locale')->value('value');

        // Загружаем рубрику с секциями и статьями, фильтруя по активности и локали
        $rubric = Rubric::with([
            'sections' => function ($query) use ($locale) {
                $query->where('activity', 1)
                    ->where('locale', $locale)
                    ->orderBy('sort', 'asc')
                    ->with(['articles' => function ($query) use ($locale) {
                        $query->where('activity', 1)
                            ->where('locale', $locale)
                            ->orderBy('sort', 'asc')
                            ->with(['images', 'tags']);
                    }]);
            }
        ])->where('url', $url)->firstOrFail();

        // Подсчет общего количества активных статей во всех секциях рубрики
        $activeArticlesCount = $rubric->sections->reduce(function ($carry, $section) {
            return $carry + ($section->articles ? $section->articles->count() : 0);
        }, 0);

        // Отдельно выбираем статьи для правого сайдбара:
        // Только активные статьи с locale, равной локали рубрики, и sidebar = true.
        $sidebarArticles = Article::where('activity', 1)
            ->where('locale', $locale)
            ->where('sidebar', true)
            ->orderBy('sort', 'asc')
            ->with(['images', 'tags'])
            ->get();

        // Отдельно выбираем статьи для главных новостей:
        // Только активные статьи с locale, равной локали рубрики, и main = true.
        $mainArticles = Article::where('activity', 1)
            ->where('locale', $locale)
            ->where('main', true)
            ->orderBy('sort', 'asc')
            ->with(['images', 'tags'])
            ->get();

        return Inertia::render('Public/Default/Rubrics/Show', [
            'rubric'              => new RubricResource($rubric),
            'sections'            => SectionResource::collection($rubric->sections),
            'sectionsCount'       => $rubric->sections->count(),
            'activeArticlesCount' => $activeArticlesCount,
            'sidebarArticles'     => ArticleResource::collection($sidebarArticles),
            'mainArticles'        => ArticleResource::collection($mainArticles),
        ]);
    }

}
