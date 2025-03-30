<?php

namespace App\Http\Controllers\Public\Default;

use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\Article\ArticleResource;
use App\Http\Resources\Admin\Banner\BannerResource;
use App\Http\Resources\Admin\Rubric\RubricResource;
use App\Http\Resources\Admin\Section\SectionResource;
use App\Models\Admin\Article\Article;
use App\Models\Admin\Banner\Banner;
use App\Models\Admin\Rubric\Rubric;
use App\Models\Admin\Setting\Setting;
use App\Traits\CacheTimeTrait;
use Illuminate\Http\JsonResponse;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Support\Facades\Cache;

class RubricController extends Controller
{
    use CacheTimeTrait;

    /**
     * Возвращает список активных рубрик в зависимости от выбранного языка.
     *
     * @return Response
     */
    public function index(): Response
    {
        $cacheTime = $this->getCacheTime();

        // Кэшируем локаль
        $locale = Cache::store('redis')->remember('setting.locale', $cacheTime, function () {
            return Setting::where('option', 'locale')->value('value');
        });

        // Кэшируем рубрики
        $rubrics = Cache::store('redis')->remember("rubrics.index.{$locale}", $cacheTime, function () use ($locale) {
            return Rubric::where('activity', 1)
                ->where('locale', $locale)
                ->orderBy('sort')
                ->get(['id', 'title', 'url', 'locale']);
        });

        return Inertia::render('Public/Default/Rubrics/Index', [
            'rubrics'      => $rubrics,
            'rubricsCount' => $rubrics->count(),
        ]);
    }

    /**
     * Страница показа рубрики
     */
    public function show(string $url): Response
    {
        $cacheTime = $this->getCacheTime();

        // Получаем локаль через кэш
        $locale = Cache::store('redis')->remember('setting.locale', $cacheTime, function () {
            return Setting::where('option', 'locale')->value('value');
        });

        // Загружаем рубрику с секциями, статьями и баннерами секций через кэш
        $rubric = Cache::store('redis')->remember("rubric.{$url}.{$locale}", $cacheTime, function () use ($url, $locale) {
            return Rubric::with([
                'sections' => function ($query) use ($locale) {
                    $query->where('activity', 1)
                        ->where('locale', $locale)
                        ->orderBy('sort', 'asc')
                        ->with([
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
                            },
                            // Загружаем баннеры, привязанные к секции
                            'banners' => function ($query) {
                                $query->where('activity', 1)
                                    ->orderBy('sort', 'desc')
                                    ->with([
                                        'images' => function ($query) {
                                            $query->orderBy('order', 'asc');
                                        }
                                    ]);
                            }
                        ]);
                }
            ])
                ->where('url', $url)
                ->firstOrFail();
        });

        // Подсчет общего количества активных статей во всех секциях рубрики
        $activeArticlesCount = $rubric->sections->reduce(function ($carry, $section) {
            return $carry + ($section->articles ? $section->articles->count() : 0);
        }, 0);

        // Кэшируем статьи для левой колонки
        $leftArticles = Cache::store('redis')->remember("articles.left.{$locale}", $cacheTime, function () use ($locale) {
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
        $mainArticles = Cache::store('redis')->remember("articles.main.{$locale}", $cacheTime, function () use ($locale) {
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
        $rightArticles = Cache::store('redis')->remember("articles.right.{$locale}", $cacheTime, function () use ($locale) {
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
        $leftBanners = Cache::store('redis')->remember("banners.left.{$locale}", $cacheTime, function () use ($locale) {
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
        $rightBanners = Cache::store('redis')->remember("banners.right.{$locale}", $cacheTime, function () use ($locale) {
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

        return Inertia::render('Public/Default/Rubrics/Show', [
            'rubric'              => new RubricResource($rubric),
            'sections'            => SectionResource::collection($rubric->sections),
            'sectionsCount'       => $rubric->sections->count(),
            'activeArticlesCount' => $activeArticlesCount,
            'leftArticles'        => ArticleResource::collection($leftArticles),
            'mainArticles'        => ArticleResource::collection($mainArticles),
            'rightArticles'       => ArticleResource::collection($rightArticles),
            'leftBanners'         => BannerResource::collection($leftBanners),
            'rightBanners'        => BannerResource::collection($rightBanners),
        ]);
    }

    /**
     * Возвращает список активных рубрик в зависимости от выбранного языка.
     *
     * @return JsonResponse
     */
    public function menuRubrics(): JsonResponse
    {
        $cacheTime = $this->getCacheTime();
        $locale = Cache::store('redis')->remember('setting.locale', $cacheTime, function () {
            return Setting::where('option', 'locale')->value('value');
        });

        $rubrics = Cache::store('redis')->remember("rubrics.menu.{$locale}", $cacheTime, function () use ($locale) {
            return Rubric::where('activity', 1)
                ->where('locale', $locale)
                ->orderBy('sort')
                ->get(['id', 'title', 'url', 'locale']);
        });

        return response()->json([
            'rubrics'      => $rubrics,
            'rubricsCount' => $rubrics->count()
        ]);
    }
}
