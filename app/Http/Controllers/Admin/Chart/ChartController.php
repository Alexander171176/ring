<?php

namespace App\Http\Controllers\Admin\Chart;

use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\Article\ArticleResource;
use App\Http\Resources\Admin\Rubric\RubricResource;
use App\Models\Admin\Article\Article;
use App\Models\Admin\Rubric\Rubric;
use App\Traits\CacheTimeTrait;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;

class ChartController extends Controller
{
    use CacheTimeTrait;

    /**
     * Display a listing of the resource.
     */
    public function index(): \Inertia\Response
    {
        $cacheTime = $this->getCacheTime();

        // Использование Redis для кэширования рубрик с количеством статей
        $rubrics = Cache::store('redis')->remember('rubrics_with_count', $cacheTime, function () {
            return Rubric::withCount('articles')->get();
        });

        $rubricsCount = $rubrics->count();

        // Использование Redis для кэширования статей с рубриками
        $articles = Cache::store('redis')->remember('articles_with_rubrics', $cacheTime, function () {
            return Article::with('rubrics')->get();
        });

        $articlesCount = $articles->count();

        return Inertia::render('Admin/Charts/Index', [
            'rubrics' => RubricResource::collection($rubrics),
            'rubricsCount' => $rubricsCount,
            'articles' => ArticleResource::collection($articles),
            'articlesCount' => $articlesCount,
        ]);
    }
}
