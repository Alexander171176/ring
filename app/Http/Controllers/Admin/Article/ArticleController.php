<?php

namespace App\Http\Controllers\Admin\Article;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Article\ArticleRequest;
use App\Http\Resources\Admin\Article\ArticleResource;
use App\Http\Resources\Admin\Rubric\RubricResource;
use App\Models\Admin\Article\Article;
use App\Models\Admin\Rubric\Rubric;
use App\Traits\CacheTimeTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class ArticleController extends Controller
{
    use CacheTimeTrait;

    /**
     * Все статьи
     *
     * @return \Inertia\Response
     */
    public function index(): \Inertia\Response
    {
        $cacheTime = $this->getCacheTime();

        $articles = Cache::store('redis')->remember('articles.all', $cacheTime, function () {
            return Article::with('rubrics')->get();
        });

        $articlesCount = Cache::store('redis')->remember('articles.count', $cacheTime, function () {
            return Article::count();
        });

        return Inertia::render('Admin/Articles/Index', [
            'articles' => ArticleResource::collection($articles),
            'articlesCount' => $articlesCount,
        ]);
    }

    /**
     * Страница создать Статью
     *
     * @return \Inertia\Response
     */
    public function create(): \Inertia\Response
    {
        $rubrics = Cache::store('redis')->remember('rubrics.all', $this->getCacheTime(), function () {
            return Rubric::all();
        });

        return Inertia::render('Admin/Articles/Create', [
            'rubrics' => RubricResource::collection($rubrics)
        ]);
    }

    /**
     * Создать статью
     *
     * @param ArticleRequest $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(ArticleRequest $request): \Illuminate\Http\RedirectResponse
    {
        $data = $request->validated();

        if ($request->hasFile('image_url')) {
            $data['image_url'] = $request->file('image_url')->store('article_images', 'public');
        }

        $rubricIds = [];
        if ($request->has('rubrics')) {
            $rubricTitles = array_column($request->input('rubrics'), 'title');
            $rubricIds = Rubric::whereIn('title', $rubricTitles)->pluck('id')->toArray();
        }

        $article = Article::create($data);

        if ($rubricIds) {
            $article->rubrics()->sync($rubricIds);
        }

        Log::info('Статья создана: ', $article->toArray());

        // Очистка кэша
        $this->clearCache(['articles.all', 'articles.count', 'rubrics.all']);

        return redirect()->route('articles.index')->with('success', 'Статья успешно создана');
    }

    /**
     * Страница редактирования Статьи
     *
     * @param string $id
     * @return \Inertia\Response
     */
    public function edit(string $id): \Inertia\Response
    {
        $cacheTime = $this->getCacheTime();

        // Находим статью с рубриками
        $article = Cache::store('redis')->remember("article.$id", $cacheTime, function () use ($id) {
            return Article::with('rubrics')->findOrFail($id);
        });

        // Проверка, есть ли изображение, и формирование правильного пути к нему
        if ($article->image_url) {
            $article->image_url = Storage::url($article->image_url);
        }

        // Получаем все рубрики
        $rubrics = Cache::store('redis')->remember('rubrics.all', $cacheTime, function () {
            return Rubric::all();
        });

        // Передаём статью и рубрики на страницу
        return Inertia::render('Admin/Articles/Edit', [
            'article' => new ArticleResource($article),
            'rubrics' => RubricResource::collection($rubrics)
        ]);
    }

    /**
     * Обновление Статьи
     *
     * @param ArticleRequest $request
     * @param string $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(ArticleRequest $request, string $id): \Illuminate\Http\RedirectResponse
    {
        $article = Article::findOrFail($id);

        $data = $request->validated();

        if ($request->hasFile('image_url')) {
            if ($article->image_url) {
                Storage::disk('public')->delete($article->image_url);
            }
            $data['image_url'] = $request->file('image_url')->store('article_images', 'public');
        } else {
            $data['image_url'] = $article->image_url;
        }

        if ($request->has('rubrics')) {
            $rubricTitles = array_column($request->input('rubrics'), 'title');
            $rubricIds = Rubric::whereIn('title', $rubricTitles)->pluck('id')->toArray();
            $article->rubrics()->sync($rubricIds);
        }

        $article->update($data);

        Log::info('Статья обновлена: ', $article->toArray());

        // Очистка кэша
        $this->clearCache(['articles.all', 'articles.count', 'rubrics.all']);

        return redirect()->route('articles.index')->with('success', 'Статья успешно обновлена');
    }

    /**
     * Удаление Статьи
     *
     * @param string $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(string $id): \Illuminate\Http\RedirectResponse
    {
        $article = Article::findOrFail($id);

        if ($article->image_url) {
            Storage::disk('public')->delete($article->image_url);
        }
        $article->delete();

        Log::info('Статья удалена: ', $article->toArray());

        // Очистка кэша
        $this->clearCache(['articles.all', 'articles.count', 'rubrics.all']);

        return back();
    }

    /**
     * Массовые действия над Статьями
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function bulkDestroy(Request $request): \Illuminate\Http\JsonResponse
    {
        $validated = $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:articles,id',
        ]);

        $articleIds = $validated['ids'];

        Article::whereIn('id', $articleIds)->each(function ($article) {
            if ($article->image_url) {
                Storage::disk('public')->delete($article->image_url);
            }
            $article->delete();
        });

        Log::info('Статьи удалены: ', $articleIds);

        // Очистка кэша
        $this->clearCache(['articles.all', 'articles.count']);

        return response()->json(['success' => true, 'reload' => true]);
    }

    /**
     * Обновление активности Статьи
     *
     * @param Request $request
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateActivity(Request $request, $id): \Illuminate\Http\JsonResponse
    {
        $validated = $request->validate([
            'activity' => 'required|boolean',
        ]);

        $article = Article::findOrFail($id);
        $article->activity = $validated['activity'];
        $article->save();

        Log::info("Обновлена активность статьи с ID: $id с данными: ", $validated);

        // Очистка кэша
        $this->clearCache(['articles.all', 'articles.count']);

        return response()->json(['success' => true, 'reload' => true]);
    }

    /**
     * Сортировка Статей
     *
     * @param Request $request
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateSort(Request $request, $id): \Illuminate\Http\JsonResponse
    {
        $validated = $request->validate([
            'sort' => 'required|integer',
        ]);

        $article = Article::findOrFail($id);
        $article->sort = $validated['sort'];
        $article->save();

        Log::info("Обновлена сортировка статьи с ID: $id с данными: ", $validated);

        // Очистка кэша
        $this->clearCache(['articles.all']);

        return response()->json(['success' => true]);
    }

    /**
     * Клонирование Статьи
     *
     * @param Request $request
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function clone(Request $request, $id): \Illuminate\Http\JsonResponse
    {
        $article = Article::findOrFail($id);

        $clonedArticle = $article->replicate();
        $clonedArticle->title = $article->title . ' 2';
        $clonedArticle->url = $article->url . '-2';
        $clonedArticle->save();

        $rubricIds = $article->rubrics->pluck('id')->toArray();
        if ($rubricIds) {
            $clonedArticle->rubrics()->sync($rubricIds);
        }

        Log::info('Статья клонирована: ', $clonedArticle->toArray());

        // Очистка кэша
        $this->clearCache(['articles.all', 'rubrics.all']);

        return response()->json(['success' => true, 'reload' => true]);
    }

}
