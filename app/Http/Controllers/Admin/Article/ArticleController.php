<?php

namespace App\Http\Controllers\Admin\Article;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Article\ArticleRequest;
use App\Http\Resources\Admin\Article\ArticleImageResource;
use App\Http\Resources\Admin\Article\ArticleResource;
use App\Http\Resources\Admin\Article\TagResource;
use App\Http\Resources\Admin\Rubric\RubricResource;
use App\Models\Admin\Article\Article;
use App\Models\Admin\Article\ArticleImage;
use App\Models\Admin\Article\Tag;
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
            return Article::with(['rubrics', 'tags', 'images'])->get();
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
        $cacheTime = $this->getCacheTime();

        // Загрузка рубрик
        $rubrics = Cache::store('redis')->remember('rubrics.all', $cacheTime, function () {
            return Rubric::all();
        });

        // Загрузка тегов
        $tags = Cache::store('redis')->remember('tags.all', $cacheTime, function () {
            return Tag::all();
        });

        // Загрузка изображений
        $images = Cache::store('redis')->remember('images.all', $cacheTime, function () {
            return ArticleImage::all();
        });

        return Inertia::render('Admin/Articles/Create', [
            'rubrics' => RubricResource::collection($rubrics),
            'tags' => TagResource::collection($tags),
            'images' => ArticleImageResource::collection($images),
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

        // ✅ Найти рубрики по title
        $rubricIds = [];
        if ($request->has('rubrics')) {
            $rubricTitles = array_column($request->input('rubrics'), 'title');
            $rubricIds = Rubric::whereIn('title', $rubricTitles)->pluck('id')->toArray();
        }

        // ✅ Найти теги по name
        $tagIds = [];
        if ($request->has('tags')) {
            $tagNames = array_column($request->input('tags'), 'name');
            $tagIds = Tag::whereIn('name', $tagNames)->pluck('id')->toArray();
        }

        // ✅ Создаем статью
        $article = Article::create($data);

        // ✅ Привязываем рубрики
        if ($rubricIds) {
            $article->rubrics()->sync($rubricIds);
        }

        // ✅ Привязываем теги
        if ($tagIds) {
            $article->tags()->sync($tagIds);
        }

        // ✅ Обрабатываем изображения (новые файлы + обновление alt/caption)
        if ($request->has('images')) {
            foreach ($data['images'] as $imageData) {
                if (isset($imageData['file']) && $imageData['file'] instanceof \Illuminate\Http\UploadedFile) {
                    // Загружаем новое изображение
                    $path = $imageData['file']->store('article_images', 'public');
                    $image = ArticleImage::create([
                        'path' => $path,
                        'alt' => $imageData['alt'] ?? '',
                        'caption' => $imageData['caption'] ?? '',
                    ]);
                    $article->images()->attach($image->id);
                } elseif (isset($imageData['id'])) {
                    // ✅ Обновляем alt и caption для существующего изображения
                    $existingImage = ArticleImage::find($imageData['id']);
                    if ($existingImage) {
                        $existingImage->update([
                            'alt' => $imageData['alt'] ?? '',
                            'caption' => $imageData['caption'] ?? '',
                        ]);
                        $article->images()->syncWithoutDetaching([$existingImage->id]);
                    }
                }
            }
        }

        return redirect()->route('articles.index')->with('success', 'Статья успешно создана.');
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

        // Находим статью с рубриками, тегами и изображениями
        $article = Article::with(['rubrics', 'tags', 'images'])->findOrFail($id);

        // Получаем все рубрики
        $rubrics = Cache::store('redis')->remember('rubrics.all', $cacheTime, function () {
            return Rubric::all();
        });

        // Получаем все теги
        $tags = Cache::store('redis')->remember('tags.all', $cacheTime, function () {
            return Tag::all();
        });

        // Передаём статью, рубрики и теги на страницу через ресурсы
        return Inertia::render('Admin/Articles/Edit', [
            'article' => new ArticleResource($article),
            'rubrics' => RubricResource::collection($rubrics),
            'tags' => TagResource::collection($tags),
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

        // ✅ Обновляем статью
        $article->update($data);

        // ✅ Привязываем рубрики
        $rubricIds = [];
        if ($request->has('rubrics')) {
            $rubricTitles = array_column($request->input('rubrics'), 'title');
            $rubricIds = Rubric::whereIn('title', $rubricTitles)->pluck('id')->toArray();
        }
        $article->rubrics()->sync($rubricIds);

        // ✅ Привязываем теги
        $tagIds = [];
        if ($request->has('tags')) {
            $tagNames = array_column($request->input('tags'), 'name');
            $tagIds = Tag::whereIn('name', $tagNames)->pluck('id')->toArray();
        }
        $article->tags()->sync($tagIds);

        // ✅ Обрабатываем изображения
        if ($request->has('images')) {
            foreach ($data['images'] as $imageData) {
                if (isset($imageData['file']) && $imageData['file'] instanceof \Illuminate\Http\UploadedFile) {
                    // Загружаем новое изображение
                    $path = $imageData['file']->store('article_images', 'public');
                    $image = ArticleImage::create([
                        'path' => $path,
                        'alt' => $imageData['alt'] ?? '',
                        'caption' => $imageData['caption'] ?? '',
                    ]);
                    $article->images()->attach($image->id);
                } elseif (isset($imageData['id'])) {
                    // ✅ Обновляем alt и caption существующего изображения
                    $existingImage = ArticleImage::find($imageData['id']);
                    if ($existingImage) {
                        $existingImage->update([
                            'alt' => $imageData['alt'] ?? '',
                            'caption' => $imageData['caption'] ?? '',
                        ]);
                        $article->images()->syncWithoutDetaching([$existingImage->id]);
                    }
                }
            }
        }

        // ✅ Очистка кэша
        $this->clearCache(['articles.all', 'articles.count', 'rubrics.all', 'tags.all']);

        return redirect()->route('articles.index')->with('success', 'Статья успешно обновлена.');
    }

    /**
     * Удаление статьи вместе с изображениями
     *
     * @param string $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(string $id): \Illuminate\Http\RedirectResponse
    {
        $article = Article::findOrFail($id);

        // ✅ Удаляем связанные изображения
        foreach ($article->images as $image) {
            // Убираем дублирование 'article_images/'
            $imagePath = $image->path; // Должно быть уже 'article_images/filename.jpg'

            // Проверяем существование файла перед удалением
            if (Storage::disk('public')->exists($imagePath)) {
                Storage::disk('public')->delete($imagePath);
                Log::info("Файл успешно удалён: {$imagePath}");
            } else {
                Log::warning("Файл не найден: {$imagePath}");
            }

            // Удаляем запись из базы данных
            $image->delete();
        }

        // ✅ Удаляем статью
        $article->delete();

        Log::info('Статья удалена: ', $article->toArray());

        // ✅ Очистка кэша
        $this->clearCache(['articles.all', 'articles.count', 'rubrics.all']);

        return back()->with('success', 'Статья и связанные изображения удалены.');
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

        // Клонирование статьи
        $clonedArticle = $article->replicate();
        $clonedArticle->title = $article->title . ' 2';
        $clonedArticle->url = $article->url . '-2';
        $clonedArticle->save();

        // Копируем рубрики
        $rubricIds = $article->rubrics->pluck('id')->toArray();
        if ($rubricIds) {
            $clonedArticle->rubrics()->sync($rubricIds);
        }

        // Копируем теги
        $tagIds = $article->tags->pluck('id')->toArray();
        if ($tagIds) {
            $clonedArticle->tags()->sync($tagIds);
        }

        // Копируем изображения
        $imageIds = $article->images->pluck('id')->toArray();
        if ($imageIds) {
            $clonedArticle->images()->sync($imageIds);
        }

        Log::info('Статья клонирована: ', $clonedArticle->toArray());

        // Очистка кэша
        $this->clearCache(['articles.all', 'rubrics.all', 'tags.all', 'images.all']);

        return response()->json(['success' => true, 'reload' => true]);
    }

}
