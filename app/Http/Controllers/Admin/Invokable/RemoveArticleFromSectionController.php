<?php

namespace App\Http\Controllers\Admin\Invokable;

use App\Http\Controllers\Controller;
use App\Models\Admin\Article\Article;
use App\Models\Admin\Section\Section;

class RemoveArticleFromSectionController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Article $article, Section $section): \Illuminate\Http\RedirectResponse
    {
        // Удаляем связь статьи с рубрикой
        $article->sections()->detach($section->id);

        return back()->with('success', 'Статья успешно удалена из секции');
    }
}
