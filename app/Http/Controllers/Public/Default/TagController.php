<?php

namespace App\Http\Controllers\Public\Default;

use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\Article\ArticleResource;
use App\Http\Resources\Admin\Banner\BannerResource;
use App\Http\Resources\Admin\Tag\TagResource;
use App\Models\Admin\Article\Article;
use App\Models\Admin\Banner\Banner;
use App\Models\Admin\Setting\Setting;
use App\Models\Admin\Tag\Tag;
use App\Traits\CacheTimeTrait;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;
use Inertia\Response;

class TagController extends Controller
{
    use CacheTimeTrait;

    /**
     * Страница показа статей по тегу.
     *
     * @param string $slug
     * @return Response
     */
    public function show(string $slug): Response
    {
        $cacheTime = $this->getCacheTime();

        // Получаем текущую локаль через кэш
        $locale = Cache::store('redis')->remember('setting.locale', $cacheTime, function () {
            return Setting::where('option', 'locale')->value('value');
        });

        // Загружаем тег с его статьями (при необходимости фильтруем по активности и локали)
        $tag = Cache::store('redis')->remember("tag.{$slug}.{$locale}", $cacheTime, function () use ($slug, $locale) {
            return Tag::with([
                'articles' => function ($query) use ($locale) {
                    $query->where('activity', 1)
                        ->where('locale', $locale)
                        ->orderBy('sort', 'desc')
                        ->with([
                            'images' => function ($query) {
                                $query->orderBy('order', 'asc');
                            },
                            'tags'
                        ]);
                }
            ])
                ->where('slug', $slug)
                ->firstOrFail();
        });

        // Кэшируем статьи для левой колонки
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

        // Кэшируем статьи для главной колонки
        $mainArticles = Cache::store('redis')
            ->remember("articles.main.{$locale}", $cacheTime, function () use ($locale) {
                return Article::where('activity', 1)
                    ->where('locale', $locale)
                    ->where('main', true)
                    ->orderBy('sort', 'desc')
                    ->with([
                        'images' => function ($query) {
                            $query->orderBy('order', 'asc');
                        },
                        'tags'
                    ])
                    ->get();
            });

        // Кэшируем статьи для правой колонки
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

        $articles = $tag->articles;
        $articlesCount = $articles->count();

        return Inertia::render('Public/Default/Tags/Show', [
            'tag'           => new TagResource($tag),
            'articles'      => ArticleResource::collection($articles),
            'articlesCount' => $articlesCount,
            'leftArticles'        => ArticleResource::collection($leftArticles),
            'mainArticles'        => ArticleResource::collection($mainArticles),
            'rightArticles'       => ArticleResource::collection($rightArticles),
            'leftBanners'         => BannerResource::collection($leftBanners),
            'rightBanners'        => BannerResource::collection($rightBanners),
        ]);
    }
}
