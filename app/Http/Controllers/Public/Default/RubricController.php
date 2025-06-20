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

        $videos = Video::where('activity', 1)
            ->orderBy('published_at', 'desc')
            ->with(['images' => fn($q) => $q->orderBy('order')])
            ->get();

        $rubric = Rubric::with([
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
                                    'images' => fn($query) => $query->orderBy('order', 'asc'),
                                    'tags'
                                ]);
                        },
                        'banners' => function ($query) {
                            $query->where('activity', 1)
                                ->orderBy('sort', 'desc')
                                ->with([
                                    'images' => fn($query) => $query->orderBy('order', 'asc'),
                                ]);
                        }
                    ]);
            }
        ])
            ->where('url', $url)
            ->firstOrFail();

        $activeArticlesCount = $rubric->sections->reduce(function ($carry, $section) {
            return $carry + ($section->articles ? $section->articles->count() : 0);
        }, 0);

        $leftArticles = Article::where('activity', 1)
            ->where('locale', $locale)
            ->where('left', true)
            ->orderBy('sort', 'desc')
            ->with(['images' => fn($q) => $q->orderBy('order'), 'tags'])
            ->get();

        $mainArticles = Article::where('activity', 1)
            ->where('locale', $locale)
            ->where('main', true)
            ->orderBy('sort', 'desc')
            ->with(['images' => fn($q) => $q->orderBy('order'), 'tags'])
            ->get();

        $rightArticles = Article::where('activity', 1)
            ->where('locale', $locale)
            ->where('right', true)
            ->orderBy('sort', 'desc')
            ->with(['images' => fn($q) => $q->orderBy('order'), 'tags'])
            ->get();

        $leftBanners = Banner::where('activity', 1)
            ->where('left', true)
            ->orderBy('sort', 'desc')
            ->with(['images' => fn($q) => $q->orderBy('order')])
            ->get();

        $rightBanners = Banner::where('activity', 1)
            ->where('right', true)
            ->orderBy('sort', 'desc')
            ->with(['images' => fn($q) => $q->orderBy('order')])
            ->get();

        $scheduledTournaments = Tournament::query()
            ->active()
            ->where('locale', $locale)
            ->scheduled()
            ->orderBy('tournament_date_time', 'desc')
            ->with([
                'fighterRed',
                'fighterBlue',
                'images' => fn($q) => $q->orderBy('order')
            ])
            ->get();

        $completedTournaments = Tournament::query()
            ->active()
            ->where('locale', $locale)
            ->completed()
            ->orderBy('tournament_date_time', 'desc')
            ->with([
                'fighterRed',
                'fighterBlue',
                'winner',
                'images' => fn($q) => $q->orderBy('order')
            ])
            ->get();

        $mainTournaments = Tournament::query()
            ->active()
            ->where('locale', $locale)
            ->where('main', true)
            ->orderBy('tournament_date_time', 'desc')
            ->with([
                'videos',
                'fighterRed',
                'fighterBlue',
                'images' => fn($q) => $q->orderBy('order'),
            ])
            ->get();

        // Получаем кастомные компоненты
        $components = config('rubrics.custom_components', []);

        // Определяем путь компонента
        $component = $components[$rubric->url] ?? 'Public/Default/Rubrics/Show';

        // Полный путь к физическому .vue-файлу
        $vuePath = resource_path("js/Pages/{$component}.vue");

        // Проверка существования .vue файла
        if (!File::exists($vuePath)) {
            $component = 'Public/Default/Rubrics/Show'; // fallback
        }

        Log::info("Компонент для рубрики '{$rubric->url}': {$component}");

        return Inertia::render($component, [
            'rubric' => new RubricResource($rubric),
            'sections' => SectionResource::collection($rubric->sections),
            'sectionsCount' => $rubric->sections->count(),
            'activeArticlesCount' => $activeArticlesCount,
            'leftArticles' => ArticleResource::collection($leftArticles),
            'mainArticles' => ArticleResource::collection($mainArticles),
            'rightArticles' => ArticleResource::collection($rightArticles),
            'leftBanners' => BannerResource::collection($leftBanners),
            'rightBanners' => BannerResource::collection($rightBanners),

            // 🔻 Добавим турниры по статусам:
            'scheduledTournaments' => TournamentResource::collection($scheduledTournaments),
            'completedTournaments' => TournamentResource::collection($completedTournaments),

            // 🔻 Добавим главный турнир
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
