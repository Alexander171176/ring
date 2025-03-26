<?php

namespace App\Http\Controllers\Admin\Invokable;

use App\Http\Controllers\Controller;
use App\Models\Admin\Article\Article;
use App\Models\Admin\Section\Section;
use Illuminate\Http\RedirectResponse;

class RemoveArticleFromSectionController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Article $article, Section $section): RedirectResponse
    {
        // Удаляем связь статьи с рубрикой
        $article->sections()->detach($section->id);

        return back()->with('success', 'Статья успешно удалена из секции');
    }
}
