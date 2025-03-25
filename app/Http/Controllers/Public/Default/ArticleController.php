<?php

namespace App\Http\Controllers\Public\Default;

use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\Article\ArticleResource;
use App\Models\Admin\Article\Article;
use App\Models\Admin\Setting\Setting;
use Inertia\Inertia;
use Inertia\Response;

class ArticleController extends Controller
{
    /**
     * Страница показа конкретной статьи.
     */
    public function show(string $url): Response
    {
        // Получаем текущую локаль из настроек
        $locale = Setting::where('option', 'locale')->value('value');

        // Загружаем статью с изображениями, тегами и рекомендованными статьями,
        // при этом для связанных статей задаём фильтр по активности и локали
        $article = Article::with([
            'images',
            'tags',
            'relatedArticles' => function ($query) use ($locale) {
                $query->where('activity', 1)->where('locale', $locale);
            },
            'relatedArticles.images' // добавляем вложенную загрузку изображений для связанных статей
        ])
            ->where('activity', 1)
            ->where('locale', $locale)
            ->where('url', $url)
            ->firstOrFail();

        // Отдельно выбираем статьи для правого сайдбара:
        $sidebarArticles = Article::where('activity', 1)
            ->where('locale', $locale)
            ->where('sidebar', true)
            ->orderBy('sort', 'asc')
            ->with(['images', 'tags'])
            ->get();

        return Inertia::render('Public/Default/Articles/Show', [
            'article'              => new ArticleResource($article),
            'sidebarArticles'      => ArticleResource::collection($sidebarArticles),
            'recommendedArticles'  => ArticleResource::collection($article->relatedArticles),
        ]);
    }
}
