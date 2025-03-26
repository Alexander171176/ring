<?php

namespace App\Http\Controllers\Admin\Invokable;

use App\Http\Controllers\Controller;
use App\Models\Admin\Article\Article;
use App\Models\Admin\Tag\Tag;
use Illuminate\Http\RedirectResponse;

class RemoveArticleFromTagController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Tag $tag, Article $article): RedirectResponse
    {
        // Удаляем связь тега со статьёй
        $tag->articles()->detach($article->id);

        return back()->with('success', 'Тег успешно удалён из статьи');
    }
}
