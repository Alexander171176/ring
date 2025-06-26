<?php

namespace App\Http\Controllers\Public\Default;

use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\Article\ArticleResource;
use App\Http\Resources\Admin\Banner\BannerResource;
use App\Http\Resources\Admin\Section\SectionResource;
use App\Http\Resources\Admin\Tournament\TournamentResource;
use App\Http\Resources\Admin\Video\VideoResource;
use App\Models\Admin\Article\Article;
use App\Models\Admin\Banner\Banner;
use App\Models\Admin\Section\Section;
use App\Models\Admin\Tournament\Tournament;
use App\Models\Admin\Video\Video;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class HomeController extends Controller
{
    public function index(): Response
    {
        // Получаем локаль напрямую из базы, без кэширования
        $locale = app()->getLocale(); // ← получаем из маршрута

        // Получаем статью напрямую без кэширования
        $latestArticles = Article::where('activity', 1)
            ->where('locale', $locale)
            ->where('left', true)
            ->with(['images' => fn($q) => $q->orderBy('order'), 'tags'])
            ->orderBy('sort', 'desc') // или ->latest()
            ->limit(4)
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

        // Получаем баннеры для левой колонки
        $mainBanners = Banner::where('activity', 1)
            ->where('main', true)
            ->orderBy('sort', 'desc')
            ->with([
                'images' => function ($query) {
                    $query->orderBy('order', 'asc');
                }
            ])
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

        $sections = Section::where('activity', 1)
            ->where('locale', $locale)
            ->orderBy('sort')
            ->with([
                'articles' => function ($query) use ($locale) {
                    $query->where('activity', 1)
                        ->where('locale', $locale)
                        ->orderBy('published_at', 'desc')
                        ->with([
                            'images' => fn($q) => $q->orderBy('order'),
                            'tags',
                        ]);
                },
            ])
            ->get();

        $videos = Video::where('activity', 1)
            ->with([
                'images' => fn($q) => $q->orderBy('order'),
            ])
            ->orderBy('published_at', 'desc')
            ->get();

        return Inertia::render('Public/Default/Index', [
            'latestArticles' => ArticleResource::collection($latestArticles),
            'mainArticles' => ArticleResource::collection($mainArticles),
            'rightArticles' => ArticleResource::collection($rightArticles),
            'mainBanners' => BannerResource::collection($mainBanners),
            'scheduledTournaments' => TournamentResource::collection($scheduledTournaments),
            'completedTournaments' => TournamentResource::collection($completedTournaments),
            'sections' => SectionResource::collection($sections),
            'videos' => VideoResource::collection($videos),
        ]);
    }
}
