<?php

namespace App\Http\Controllers\Public\Default;

use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\Article\ArticleResource;
use App\Http\Resources\Admin\Banner\BannerResource;
use App\Models\Admin\Article\Article;
use App\Models\Admin\Banner\Banner;
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

        return Inertia::render('Public/Default/Index', [
            'latestArticles' => ArticleResource::collection($latestArticles),
            'mainArticles' => ArticleResource::collection($mainArticles),
            'rightArticles' => ArticleResource::collection($rightArticles),
            'mainBanners'        => BannerResource::collection($mainBanners),
        ]);
    }
}
