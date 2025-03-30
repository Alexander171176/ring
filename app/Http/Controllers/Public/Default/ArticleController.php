<?php

namespace App\Http\Controllers\Public\Default;

use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\Article\ArticleResource;
use App\Http\Resources\Admin\Banner\BannerResource;
use App\Models\Admin\Article\Article;
use App\Models\Admin\Banner\Banner;
use App\Models\Admin\Setting\Setting;
use App\Traits\CacheTimeTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;
use Inertia\Response;

class ArticleController extends Controller
{

    use CacheTimeTrait;

    /**
     * Страница показа конкретной статьи.
     */
    public function show(string $url): Response
    {
        $cacheTime = $this->getCacheTime();

        // Кэшируем локаль
        $locale = Cache::store('redis')->remember('setting.locale', $cacheTime, function () {
            return Setting::where('option', 'locale')->value('value');
        });

        // Кэшируем статью
        $article = Cache::store('redis')
            ->remember("article.{$url}.{$locale}", $cacheTime, function () use ($url, $locale) {
            return Article::with([
                'images' => function ($query) {
                    $query->orderBy('order', 'asc');
                },
                'tags',
                'relatedArticles' => function ($query) use ($locale) {
                    $query->where('activity', 1)
                        ->where('locale', $locale);
                },
                'relatedArticles.images' => function ($query) {
                    $query->orderBy('order', 'asc');
                }
            ])
                ->where('activity', 1)
                ->where('locale', $locale)
                ->where('url', $url)
                ->firstOrFail();
        });

        // Обновляем количество просмотров (не кэшируем обновления)
        $article->increment('views');

        // Кэшируем статьи для левого сайдбара
        $leftArticles = Cache::store('redis')
            ->remember("articles.left.{$locale}", $cacheTime, function () use ($locale) {
            return Article::where('activity', 1)
                ->where('locale', $locale)
                ->where('left', true)
                ->orderBy('sort', 'desc')
                ->with([
                    'images' => function ($query) {
                        $query->orderBy('order', 'asc');
                    },
                    'tags'
                ])
                ->get();
        });

        // Кэшируем статьи для правого сайдбара
        $rightArticles = Cache::store('redis')
            ->remember("articles.right.{$locale}", $cacheTime, function () use ($locale) {
            return Article::where('activity', 1)
                ->where('locale', $locale)
                ->where('right', true)
                ->orderBy('sort', 'desc')
                ->with([
                    'images' => function ($query) {
                        $query->orderBy('order', 'asc');
                    },
                    'tags'
                ])
                ->get();
        });

        // Кэшируем баннеры для левой колонки
        $leftBanners = Cache::store('redis')
            ->remember("banners.left.{$locale}", $cacheTime, function () use ($locale) {
            return Banner::where('activity', 1)
                ->where('left', true)
                ->orderBy('sort', 'desc')
                ->with([
                    'images' => function ($query) {
                        $query->orderBy('order', 'asc');
                    }
                ])
                ->get();
        });

        // Кэшируем баннеры для правой колонки
        $rightBanners = Cache::store('redis')
            ->remember("banners.right.{$locale}", $cacheTime, function () use ($locale) {
            return Banner::where('activity', 1)
                ->where('right', true)
                ->orderBy('sort', 'desc')
                ->with([
                    'images' => function ($query) {
                        $query->orderBy('order', 'asc');
                    }
                ])
                ->get();
        });

        return Inertia::render('Public/Default/Articles/Show', [
            'article'             => new ArticleResource($article),
            'leftArticles'        => ArticleResource::collection($leftArticles),
            'rightArticles'       => ArticleResource::collection($rightArticles),
            'recommendedArticles' => ArticleResource::collection($article->relatedArticles),
            'leftBanners'         => BannerResource::collection($leftBanners),
            'rightBanners'        => BannerResource::collection($rightBanners),
        ]);
    }

    /**
     * Лайк статьи
     *
     * @param string $id
     * @return JsonResponse
     */
    public function like(string $id): JsonResponse
    {
        $article = Article::findOrFail($id);
        $article->increment('likes');
        return response()->json([
            'success' => true,
            'likes'   => $article->likes,
        ]);
    }
}
