<?php

namespace App\Http\Controllers\Public\Default;

use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\Tournament\TournamentResource;
use App\Models\Admin\Athlete\Athlete;
use App\Models\Admin\Tournament\Tournament;
use App\Models\Admin\Video\Video;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;
use Inertia\Response;

class TournamentController extends Controller
{
    /**
     * Страница показа видео
     */
    public function show(string $url): Response
    {
        $locale = app()->getLocale();
        $perPage = 4;
        $currentPageScheduled = request()->input('page_scheduled', 1);
        $currentPageCompleted = request()->input('page_completed', 1);
        $cacheMinutes = 30;

        $tournament = Tournament::where('url', $url)
            ->where('activity', 1)
            ->with([
                'images' => fn($q) => $q->orderBy('order'),
                'fighterRed', 'fighterBlue', 'winner', 'videos',
            ])
            ->firstOrFail();

        // Получаем и кэшируем все активные турниры
        $allTournaments = Cache::remember("tournaments:{$locale}", $cacheMinutes, function () use ($locale) {
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

        // Фильтрация по статусу
        $scheduledAll = $allTournaments->where('status', 'scheduled')->values();
        $completedAll = $allTournaments->where('status', 'completed')->values();

        // Пагинация
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

        return Inertia::render('Public/Default/Tournaments/Show', [
            'tournament' => new TournamentResource($tournament),
            'scheduledTournaments' => TournamentResource::collection($paginatedScheduled),
            'completedTournaments' => TournamentResource::collection($paginatedCompleted),
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
}
