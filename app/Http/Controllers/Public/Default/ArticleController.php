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

        // Получаем конкретную статью, фильтруя по активности, локали и URL
        $article = Article::with(['images', 'tags'])
            ->where('activity', 1)
            ->where('locale', $locale)
            ->where('url', $url)
            ->firstOrFail();

        // Отдельно выбираем статьи для правого сайдбара:
        // Только активные статьи с locale, равной локали рубрики, и sidebar = true.
        $sidebarArticles = Article::where('activity', 1)
            ->where('locale', $locale)
            ->where('sidebar', true)
            ->orderBy('sort', 'asc')
            ->with(['images', 'tags'])
            ->get();

        return Inertia::render('Public/Default/Articles/Show', [
            'article' => new ArticleResource($article),
            'sidebarArticles'     => ArticleResource::collection($sidebarArticles),
        ]);
    }
}
