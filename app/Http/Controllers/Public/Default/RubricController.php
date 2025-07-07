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
use Illuminate\Pagination\LengthAwarePaginator;
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
    public function show(Request $request, string $url): Response
    {
        $locale = app()->getLocale();
        $cacheMinutes = 10;
        $search = trim($request->input('search'));
        $normalizedSearch = str_replace(' ', '-', mb_strtolower($search)); // 👈 добавлено

        $currentPageArticles = (int) $request->input('page_articles', 1);
        $currentPageScheduled = (int) $request->input('page_scheduled', 1);
        $currentPageCompleted = (int) $request->input('page_completed', 1);

        $perPage = 8;

        $rubric = Rubric::with([
            'sections' => fn($q) => $q
                ->where('activity', 1)
                ->where('locale', $locale)
                ->orderBy('sort')
        ])->where('url', $url)->firstOrFail();

        $allArticles = $rubric->sections->flatMap(function ($section) use ($locale, $search) {
            return $section->articles()
                ->where('activity', 1)
                ->where('locale', $locale)
                ->when($search, fn($q) => $q->where('title', 'like', "%$search%"))
                ->with([
                    'images' => fn($q) => $q->orderBy('order'),
                    'tags'
                ])
                ->get();
        });

        $allArticles = $allArticles->sortByDesc('published_at')->values();

        $paginatedArticles = new LengthAwarePaginator(
            $allArticles->forPage($currentPageArticles, $perPage),
            $allArticles->count(),
            $perPage,
            $currentPageArticles,
            [
                'path' => request()->url(),
                'pageName' => 'page_articles',
                'query' => request()->query(),
            ]
        );

        $leftArticles = $paginatedArticles->where('left', true)->values();
        $mainArticles = $paginatedArticles->where('main', true)->values();
        $rightArticles = $paginatedArticles->where('right', true)->values();

        $leftBanners = Cache::remember("banners:left", $cacheMinutes, fn() =>
        Banner::where('activity', 1)->where('left', true)
            ->with(['images' => fn($q) => $q->orderBy('order')])
            ->orderBy('sort')->get()
        );

        $rightBanners = Cache::remember("banners:right", $cacheMinutes, fn() =>
        Banner::where('activity', 1)->where('right', true)
            ->with(['images' => fn($q) => $q->orderBy('order')])
            ->orderBy('sort')->get()
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

        // ✅ Теперь поиск по name турниров с тире
        $allTournaments = $search
            ? Tournament::active()
                ->where('locale', $locale)
                ->where('name', 'like', "%{$normalizedSearch}%")
                ->with([
                    'fighterRed',
                    'fighterBlue',
                    'winner',
                    'videos',
                    'images' => fn($q) => $q->orderBy('order'),
                ])
                ->orderByDesc('tournament_date_time')
                ->get()
            : Cache::remember("tournaments:{$locale}", $cacheMinutes, function () use ($locale) {
                return Tournament::active()
                    ->where('locale', $locale)
                    ->with([
                        'fighterRed',
                        'fighterBlue',
                        'winner',
                        'videos',
                        'images' => fn($q) => $q->orderBy('order'),
                    ])
                    ->orderByDesc('tournament_date_time')
                    ->get();
            });

        $scheduledAll = $allTournaments->filter(function ($t) use ($search) {
            $tournamentName = mb_strtolower(str_replace('-', ' ', $t->name));
            $searchTerm = mb_strtolower($search);
            return $t->status === 'scheduled' &&
                (!$search || str_contains($tournamentName, $searchTerm));
        })->values();

        $completedAll = $allTournaments->filter(function ($t) use ($search) {
            $tournamentName = mb_strtolower(str_replace('-', ' ', $t->name));
            $searchTerm = mb_strtolower($search);
            return $t->status === 'completed' &&
                (!$search || str_contains($tournamentName, $searchTerm));
        })->values();

        $paginatedScheduled = new LengthAwarePaginator(
            $scheduledAll->forPage($currentPageScheduled, $perPage),
            $scheduledAll->count(),
            $perPage,
            $currentPageScheduled,
            [
                'path' => request()->url(),
                'pageName' => 'page_scheduled',
                'query' => request()->query(),
            ]
        );

        $paginatedCompleted = new LengthAwarePaginator(
            $completedAll->forPage($currentPageCompleted, $perPage),
            $completedAll->count(),
            $perPage,
            $currentPageCompleted,
            [
                'path' => request()->url(),
                'pageName' => 'page_completed',
                'query' => request()->query(),
            ]
        );

        $mainTournaments = $allTournaments->filter(fn($t) => $t->main === true)->values();

        $videos = Cache::remember("videos:all", $cacheMinutes, fn() =>
        Video::where('activity', 1)
            ->with(['images' => fn($q) => $q->orderBy('order')])
            ->orderBy('published_at', 'desc')
            ->get()
        );

        $activeArticlesCount = $allArticles->count();

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
            'articles' => ArticleResource::collection($paginatedArticles),
            'pagination' => [
                'currentPage' => $paginatedArticles->currentPage(),
                'lastPage' => $paginatedArticles->lastPage(),
                'perPage' => $paginatedArticles->perPage(),
                'total' => $paginatedArticles->total(),
            ],
            'scheduledTournaments' => TournamentResource::collection($paginatedScheduled),
            'completedTournaments' => TournamentResource::collection($paginatedCompleted),
            'mainTournaments' => TournamentResource::collection($mainTournaments),
            'videos' => VideoResource::collection($videos),
            'locale' => $locale,
            'activeArticlesCount' => $activeArticlesCount,
            'filters' => [
                'search' => $search,
            ],
            'leftArticles' => ArticleResource::collection($leftArticles),
            'mainArticles' => ArticleResource::collection($mainArticles),
            'rightArticles' => ArticleResource::collection($rightArticles),
            'leftBanners' => BannerResource::collection($leftBanners),
            'rightBanners' => BannerResource::collection($rightBanners),
            'scheduledPagination' => [
                'currentPage' => $paginatedScheduled->currentPage(),
                'lastPage' => $paginatedScheduled->lastPage(),
                'perPage' => $paginatedScheduled->perPage(),
                'total' => $paginatedScheduled->total(),
            ],
            'completedPagination' => [
                'currentPage' => $paginatedCompleted->currentPage(),
                'lastPage' => $paginatedCompleted->lastPage(),
                'perPage' => $paginatedCompleted->perPage(),
                'total' => $paginatedCompleted->total(),
            ],
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
