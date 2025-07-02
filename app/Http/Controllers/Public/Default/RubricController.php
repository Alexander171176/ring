<?php

namespace App\Http\Controllers\Public\Default;

use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\Article\ArticleResource;
use App\Http\Resources\Admin\Banner\BannerResource;
use App\Http\Resources\Admin\Rubric\RubricResource;
use App\Http\Resources\Admin\Section\SectionResource;
use App\Http\Resources\Admin\Tournament\TournamentResource;
use App\Http\Resources\Admin\Video\VideoResource;
use App\Models\Admin\Article\Article;
use App\Models\Admin\Banner\Banner;
use App\Models\Admin\Rubric\Rubric;
use App\Models\Admin\Section\Section;
use App\Models\Admin\Setting\Setting;
use App\Models\Admin\Tournament\Tournament;
use App\Models\Admin\Video\Video;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response;

class RubricController extends Controller
{
    /**
     * Возвращает список активных рубрик в зависимости от выбранного языка.
     *
     * @return Response
     */
    public function index(): Response
    {
        $locale = app()->getLocale(); // ← получаем из маршрута

        $rubrics = Rubric::where('activity', 1)
            ->where('locale', $locale)
            ->orderBy('sort')
            ->get(['id', 'title', 'url', 'locale']);

        return Inertia::render('Public/Default/Rubrics/Index', [
            'rubrics' => $rubrics,
            'rubricsCount' => $rubrics->count(),
        ]);
    }

    /**
     * Страница показа рубрики
     */
    public function show(string $url): Response
    {
        $locale = app()->getLocale();
        $cacheMinutes = 10;

        $rubric = Cache::remember("rubric:{$url}:{$locale}", $cacheMinutes, function () use ($url, $locale) {
            return Rubric::with([
                'sections' => function ($query) use ($locale) {
                    $query->where('activity', 1)
                        ->where('locale', $locale)
                        ->orderBy('sort')
                        ->with([
                            'articles' => function ($query) use ($locale) {
                                $query->where('activity', 1)
                                    ->where('locale', $locale)
                                    ->orderBy('sort', 'desc')
                                    ->with(['images' => fn($q) => $q->orderBy('order'), 'tags']);
                            },
                        ]);
                }
            ])->where('url', $url)->firstOrFail();
        });

        $articles = Cache::remember("articles:{$locale}", $cacheMinutes, function () use ($locale) {
            return Article::where('activity', 1)
                ->where('locale', $locale)
                ->where(fn($q) => $q->where('left', true)->orWhere('main', true)->orWhere('right', true))
                ->with(['images' => fn($q) => $q->orderBy('order'), 'tags'])
                ->orderBy('sort', 'desc')
                ->get();
        });

        $leftArticles = $articles->where('left', true)->values();
        $mainArticles = $articles->where('main', true)->values();
        $rightArticles = $articles->where('right', true)->values();

        $leftBanners = Cache::remember("banners:left", $cacheMinutes, fn() =>
        Banner::where('activity', 1)->where('left', true)
            ->with(['images' => fn($q) => $q->orderBy('order')])->orderBy('sort')->get()
        );

        $rightBanners = Cache::remember("banners:right", $cacheMinutes, fn() =>
        Banner::where('activity', 1)->where('right', true)
            ->with(['images' => fn($q) => $q->orderBy('order')])->orderBy('sort')->get()
        );

        $sectionBanners = Cache::remember("banners:sections:{$locale}", $cacheMinutes, function () use ($locale) {
            return Banner::where('activity', 1)
                ->whereHas('sections', fn($q) => $q->where('activity', 1)->where('locale', $locale))
                ->with([
                    'images' => fn($q) => $q->orderBy('order'),
                    'sections' => fn($q) => $q->where('activity', 1)->where('locale', $locale),
                ])
                ->orderBy('sort')
                ->get();
        });

        $allTournaments = Cache::remember("tournaments:{$locale}", $cacheMinutes, function () use ($locale) {
            return Tournament::query()
                ->active()
                ->where('locale', $locale)
                ->with(['fighterRed', 'fighterBlue', 'winner', 'videos', 'images' => fn($q) => $q->orderBy('order')])
                ->orderBy('tournament_date_time', 'desc')
                ->get();
        });

        $scheduledTournaments = $allTournaments->filter(fn($t) => $t->status === 'scheduled')->values();
        $completedTournaments = $allTournaments->filter(fn($t) => $t->status === 'completed')->values();
        $mainTournaments = $allTournaments->filter(fn($t) => $t->main === true)->values();

        $videos = Cache::remember("videos:all", $cacheMinutes, fn() =>
        Video::where('activity', 1)
            ->with(['images' => fn($q) => $q->orderBy('order')])
            ->orderBy('published_at', 'desc')
            ->get()
        );

        $activeArticlesCount = $rubric->sections->sum(fn($section) => $section->articles->count());

        $components = config('rubrics.custom_components', []);
        $component = $components[$rubric->url] ?? 'Public/Default/Rubrics/Show';
        $vuePath = resource_path("js/Pages/{$component}.vue");
        if (!File::exists($vuePath)) {
            $component = 'Public/Default/Rubrics/Show';
        }

        return Inertia::render($component, [
            'rubric' => new RubricResource($rubric),
            'sections' => SectionResource::collection($rubric->sections),
            'sectionBanners' => BannerResource::collection($sectionBanners),
            'sectionsCount' => $rubric->sections->count(),
            'activeArticlesCount' => $activeArticlesCount,
            'leftArticles' => ArticleResource::collection($leftArticles),
            'mainArticles' => ArticleResource::collection($mainArticles),
            'rightArticles' => ArticleResource::collection($rightArticles),
            'leftBanners' => BannerResource::collection($leftBanners),
            'rightBanners' => BannerResource::collection($rightBanners),
            'scheduledTournaments' => TournamentResource::collection($scheduledTournaments),
            'completedTournaments' => TournamentResource::collection($completedTournaments),
            'mainTournaments' => TournamentResource::collection($mainTournaments),
            'videos' => VideoResource::collection($videos),
        ]);
    }

    /**
     * Возвращает список активных рубрик в зависимости от выбранного языка.
     *
     * @return JsonResponse
     */
    public function menuRubrics(): JsonResponse
    {
        $locale = app()->getLocale(); // ← получаем из маршрута

        $rubrics = Rubric::where('activity', 1)
            ->where('locale', $locale)
            ->orderBy('sort')
            ->get(['id', 'title', 'url', 'locale']);

        return response()->json([
            'locale' => $locale,
            'rubrics' => $rubrics,
            'rubricsCount' => $rubrics->count(),
        ]);
    }

}
