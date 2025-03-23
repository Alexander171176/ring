<?php

namespace App\Http\Controllers\Admin\Chart;

use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\Article\ArticleResource;
use App\Http\Resources\Admin\Rubric\RubricResource;
use App\Models\Admin\Article\Article;
use App\Models\Admin\Rubric\Rubric;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ChartController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): Response
    {
        // Загружаем рубрики с секциями, для которых вычисляем количество статей
        $rubrics = Rubric::with([
            'sections' => function ($query) {
                $query->withCount('articles'); // для каждой секции подсчитываем количество связанных статей
            }
        ])->get();

        // Для каждой рубрики суммируем количество статей из всех секций
        foreach ($rubrics as $rubric) {
            $rubric->articles_count = $rubric->sections->sum('articles_count');
        }

        $rubricsCount = $rubrics->count();

        // Загружаем статьи (при необходимости с секциями)
        $articles = Article::with('sections')->get();
        $articlesCount = $articles->count();

        return Inertia::render('Admin/Charts/Index', [
            'rubrics'       => RubricResource::collection($rubrics),
            'rubricsCount'  => $rubricsCount,
            'articles'      => ArticleResource::collection($articles),
            'articlesCount' => $articlesCount,
        ]);
    }
}
