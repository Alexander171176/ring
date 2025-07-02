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
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;
use Inertia\Response;

class HomeController extends Controller
{
    public function index(): Response
    {
        $locale = app()->getLocale();

        // 🔄 Статьи по флагам
        $allArticles = Cache::remember("home_articles_{$locale}", now()->addMinutes(10), function () use ($locale) {
            return Article::query()
                ->where('activity', 1)
                ->where('locale', $locale)
                ->where(function ($q) {
                    $q->where('left', true)
                        ->orWhere('main', true)
                        ->orWhere('right', true);
                })
                ->with([
                    'images' => fn($q) => $q->orderBy('order'),
                    'tags',
                ])
                ->orderBy('sort', 'desc')
                ->get();
        });

        $latestArticles = $allArticles->where('left', true)->take(4)->values();
        $mainArticles   = $allArticles->where('main', true)->values();
        $rightArticles  = $allArticles->where('right', true)->values();

        // 🏁 Баннеры
        $mainBanners = Cache::remember("home_banners_main", now()->addMinutes(10), fn() =>
        Banner::where('activity', 1)
            ->where('main', true)
            ->with(['images' => fn($q) => $q->orderBy('order')])
            ->orderBy('sort', 'desc')
            ->get()
        );

        // 🥊 Турниры
        $tournaments = Cache::remember("home_tournaments_{$locale}", now()->addMinutes(10), fn() =>
        Tournament::query()
            ->active()
            ->whereIn('status', ['scheduled', 'completed'])
            ->with([
                'fighterRed',
                'fighterBlue',
                'winner',
                'images' => fn($q) => $q->orderBy('order'),
            ])
            ->orderBy('tournament_date_time', 'desc')
            ->get()
        );

        $scheduledTournaments = $tournaments->filter(fn($t) => $t->status === 'scheduled')->take(2)->values();
        $completedTournaments = $tournaments->filter(fn($t) => $t->status === 'completed')->take(2)->values();

        // 🧩 Секции с 4 последними статьями
        $sections = Cache::remember("home_sections_{$locale}", now()->addMinutes(10), function () use ($locale) {
            $sections = Section::query()
                ->where('activity', 1)
                ->where('locale', $locale)
                ->orderBy('sort')
                ->get();

            // вручную добавляем только 4 статьи в каждую секцию
            foreach ($sections as $section) {
                $section->setRelation('articles', $section->articles()
                    ->where('activity', 1)
                    ->where('locale', $locale)
                    ->with([
                        'images' => fn($q) => $q->orderBy('order'),
                        'tags',
                    ])
                    ->orderBy('published_at', 'desc')
                    ->limit(4)
                    ->get());
            }

            return $sections;
        });

        // 🎥 Видео
        $videos = Cache::remember("home_videos", now()->addMinutes(10), fn() =>
        Video::query()
            ->where('activity', 1)
            ->with(['images' => fn($q) => $q->orderBy('order')])
            ->orderBy('published_at', 'desc')
            ->get()
        );

        return Inertia::render('Public/Default/Index', [
            'latestArticles'        => ArticleResource::collection($latestArticles),
            'mainArticles'          => ArticleResource::collection($mainArticles),
            'rightArticles'         => ArticleResource::collection($rightArticles),
            'mainBanners'           => BannerResource::collection($mainBanners),
            'scheduledTournaments'  => TournamentResource::collection($scheduledTournaments),
            'completedTournaments'  => TournamentResource::collection($completedTournaments),
            'sections'              => SectionResource::collection($sections),
            'videos'                => VideoResource::collection($videos),
        ]);
    }
}
