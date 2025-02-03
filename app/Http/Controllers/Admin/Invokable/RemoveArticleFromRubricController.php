<?php

namespace App\Http\Controllers\Admin\Invokable;

use App\Http\Controllers\Controller;
use App\Models\Admin\Article\Article;
use App\Models\Admin\Rubric\Rubric;
use Illuminate\Http\Request;

class RemoveArticleFromRubricController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Article $article, Rubric $rubric): \Illuminate\Http\RedirectResponse
    {
        // Удаляем связь статьи с рубрикой
        $article->rubrics()->detach($rubric->id);

        return back()->with('success', 'Статья успешно удалена из рубрики');
    }
}
