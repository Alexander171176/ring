<?php

namespace App\Http\Controllers\Admin\Invokable;

use App\Http\Controllers\Controller;
use App\Models\Admin\Article\Article;
use App\Models\Admin\Section\Section;
use App\Models\Admin\Video\Video;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class RemoveArticleFromVideoController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Video $video, Article $article): RedirectResponse
    {
        // Удаляем связь видео с постом
        $video->articles()->detach($article->id);

        return back()->with('success', 'Видео успешно удалено из статьи');
    }
}
