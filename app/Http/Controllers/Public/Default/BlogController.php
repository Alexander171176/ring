<?php

namespace App\Http\Controllers\Public\Default;

use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\Article\ArticleResource;
use App\Http\Resources\Admin\Page\PageResource;
use App\Http\Resources\Admin\Rubric\RubricResource;
use App\Models\Admin\Article\Article;
use App\Models\Admin\Page\Page;
use App\Models\Admin\Rubric\Rubric;
use App\Traits\CacheTimeTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;

class BlogController extends Controller
{
    use CacheTimeTrait;

    /**
     * Страница Блога.
     */
    public function index(): \Inertia\Response
    {
        $cacheTime = $this->getCacheTime();

        $blogPage = Cache::store('redis')->remember('pages.blog', $cacheTime, function () {
            return Page::where('url', 'blog')->first();
        });

        // Добавляем подсчет комментариев для каждой статьи
        $articles = Cache::store('redis')->tags(['articles'])->remember('articles.all', $cacheTime, function () {
            return Article::where('activity', true)
                ->withCount('comments') // Подсчет комментариев
                ->orderBy('sort', 'asc')
                ->get();
        });

        $rubrics = Cache::store('redis')->tags(['rubrics'])->remember('rubrics.all', $cacheTime, function () {
            return Rubric::where('activity', true)->orderBy('sort', 'asc')->get();
        });

        return Inertia::render('Templates/Default/pages/Blog/Index', [
            'page' => $blogPage ? new PageResource($blogPage) : null,
            'articles' => ArticleResource::collection($articles),
            'rubrics' => RubricResource::collection($rubrics),
            'auth' => auth()->user(),
        ]);
    }

    /**
     * Страница Статьи.
     *
     * @param string $url URL of the article
     * @return \Inertia\Response
     */
    public function show(string $url): \Inertia\Response
    {
        $cacheTime = $this->getCacheTime();

        // Получение статьи по URL из кеша
        $article = Cache::store('redis')->tags(['articles'])->remember("article.{$url}", $cacheTime, function () use ($url) {
            return Article::where('url', $url)->firstOrFail();
        });

        // Инкрементировать количество просмотров рубрик
        $article->increment('views');

        return Inertia::render('Templates/Default/pages/Blog/Show', [
            'article' => new ArticleResource($article),
            'auth' => auth()->user(),
        ]);
    }

    /**
     * Страница Рубрики.
     *
     * @param string $url URL of the rubric
     * @return \Inertia\Response
     */
    public function showRubric(string $url): \Inertia\Response
    {
        $cacheTime = $this->getCacheTime();

        // Получение рубрики по URL из кеша
        $rubric = Cache::store('redis')->tags(['rubrics'])->remember("rubric.{$url}", $cacheTime, function () use ($url) {
            return Rubric::where('url', $url)
                ->with(['articles' => function ($query) {
                    $query->where('activity', true)
                        ->withCount('comments') // Подсчет комментариев для статей в рубрике
                        ->orderBy('sort', 'asc');
                }])
                ->firstOrFail();
        });

        $rubric->increment('views');

        // Получаем активные рубрики для отображения
        $rubrics = Cache::store('redis')->tags(['rubrics'])->remember('rubrics.active', $cacheTime, function () {
            return Rubric::where('activity', true)->orderBy('sort', 'asc')->get();
        });

        return Inertia::render('Templates/Default/pages/Blog/Rubric', [
            'rubric' => new RubricResource($rubric),
            'rubrics' => RubricResource::collection($rubrics),
            'articles' => ArticleResource::collection($rubric->articles),
            'rubricId' => $rubric->id,
            'auth' => auth()->user(),
        ]);
    }

    /**
     * Проверка лайка статьи пользователем.
     *
     * @param $id
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function isLiked($id, Request $request): \Illuminate\Http\JsonResponse
    {
        // Получаем статью
        $article = Article::findOrFail($id);

        // Получаем ID пользователя из запроса (переданный с фронтенда)
        $userId = $request->input('user_id');

        // Если ID пользователя не передан, вернуть 'liked' => false
        if (!$userId) {
            return response()->json(['liked' => false]);
        }

        // Проверяем, лайкнул ли пользователь статью
        $liked = $article->likes()->where('user_id', $userId)->exists();

        // Возвращаем результат
        return response()->json(['liked' => $liked]);
    }

    /**
     * Метод для обработки лайков.
     *
     * @param $id
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function like($id, Request $request): \Illuminate\Http\JsonResponse
    {
        // Проверяем, передан ли user_id
        if (!$request->has('user_id')) {
            return response()->json(['message' => 'User ID is required'], 400);
        }

        // Получаем статью
        $article = Article::findOrFail($id);

        $userId = $request->input('user_id');

        // Проверка, лайкнул ли уже пользователь статью
        $alreadyLiked = $article->likes()->where('user_id', $userId)->exists();

        if ($alreadyLiked) {
            return response()->json(['message' => 'You have already liked this article'], 400);
        }

        // Сохраняем лайк в таблицу article_likes
        $article->likes()->create(['user_id' => $userId]);

        // Инкрементируем счетчик лайков
        $article->increment('likes');

        return response()->json(['likes' => $article->likes()->count()]);
    }

}
