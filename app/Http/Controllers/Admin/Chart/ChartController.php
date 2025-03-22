<?php

namespace App\Http\Controllers\Admin\Chart;

use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\Article\ArticleResource;
use App\Http\Resources\Admin\Rubric\RubricResource;
use App\Models\Admin\Article\Article;
use App\Models\Admin\Rubric\Rubric;
use Inertia\Inertia;
use Inertia\Response;

class ChartController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): Response
    {
        // Прямые запросы без кэширования
        $rubrics = Rubric::withCount('articles')->get();
        $rubricsCount = $rubrics->count();

        $articles = Article::with('rubrics')->get();
        $articlesCount = $articles->count();

        return Inertia::render('Admin/Charts/Index', [
            'rubrics'       => RubricResource::collection($rubrics),
            'rubricsCount'  => $rubricsCount,
            'articles'      => ArticleResource::collection($articles),
            'articlesCount' => $articlesCount,
        ]);
    }
}
