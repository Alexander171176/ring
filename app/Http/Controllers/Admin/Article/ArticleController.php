<?php

namespace App\Http\Controllers\Admin\Article;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Article\ArticleRequest;
use App\Http\Resources\Admin\Article\ArticleImageResource;
use App\Http\Resources\Admin\Article\ArticleResource;
use App\Http\Resources\Admin\Section\SectionResource;
use App\Http\Resources\Admin\Tag\TagResource;
use App\Models\Admin\Article\Article;
use App\Models\Admin\Article\ArticleImage;
use App\Models\Admin\Section\Section;
use App\Models\Admin\Tag\Tag;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response;

class ArticleController extends Controller
{
    /**
     * Все статьи
     *
     * @return Response
     */
    public function index(): Response
    {
        $articles = Article::with(['sections', 'tags', 'images'])->get();
        $articlesCount = Article::count();

        return Inertia::render('Admin/Articles/Index', [
            'articles' => ArticleResource::collection($articles),
            'articlesCount' => $articlesCount,
        ]);
    }

    /**
     * Страница создать Статью
     *
     * @return Response
     */
    public function create(): Response
    {
        // Загрузка рубрик
        $sections = Section::all();

        // Загрузка тегов
        $tags = Tag::all();

        // Загрузка изображений
        $images = ArticleImage::all();

        // Загрузка всех статей для выбора в рекомендованных (или нужный поднабор)
        $allArticles = Article::select('id', 'title')->get();

        return Inertia::render('Admin/Articles/Create', [
            'sections' => SectionResource::collection($sections),
            'tags' => TagResource::collection($tags),
            'images' => ArticleImageResource::collection($images),
            'related_articles' => $allArticles,
        ]);
    }

    /**
     * Создать статью
     *
     * @param ArticleRequest $request
     * @return RedirectResponse
     */
    public function store(ArticleRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $imagesData = $data['images'] ?? [];
        unset($data['images']);

        // Создаем статью
        $article = Article::create($data);

        // Синхронизация рубрик и тегов
        if (!empty($data['sections'])) {
            $sectionIds = Section::whereIn('title', array_column($data['sections'], 'title'))->pluck('id')->toArray();
            $article->sections()->sync($sectionIds);
        }

        if (!empty($data['tags'])) {
            $tagIds = Tag::whereIn('name', array_column($data['tags'], 'name'))->pluck('id')->toArray();
            $article->tags()->sync($tagIds);
        }

        // Связанные статьи
        if (!empty($data['related_articles'])) {
            $relatedIds = Article::whereIn('title', array_column($data['related_articles'], 'title'))
                ->where('id', '<>', $article->id)
                ->pluck('id')->toArray();
            $article->relatedArticles()->sync($relatedIds);
        }

        // Обработка изображений через библиотеку spatie
        foreach ($imagesData as $imageData) {
            $image = ArticleImage::create([
                'order' => $imageData['order'] ?? 0,
                'alt' => $imageData['alt'] ?? '',
                'caption' => $imageData['caption'] ?? '',
            ]);

            if (!empty($imageData['file'])) {
                $image->addMedia($imageData['file'])->toMediaCollection('images');
            }

            $article->images()->attach($image->id);
        }

        return redirect()->route('articles.index')->with('success', 'Статья успешно создана.');
    }

    /**
     * Страница редактирования Статьи
     *
     * @param string $id
     * @return Response
     */
    public function edit(string $id): Response
    {
        // Находим статью с рубриками, тегами, изображениями и связанными статьями
        $article = Article::with(['sections', 'tags', 'images', 'relatedArticles'])->findOrFail($id);

        // Получаем все рубрики
        $sections = Section::all();

        // Получаем все теги
        $tags = Tag::all();

        // Загружаем все статьи для мультиселекта (исключая текущую)
        $allArticles = Article::where('id', '<>', $article->id)->select('id', 'title')->get();

        return Inertia::render('Admin/Articles/Edit', [
            'article' => new ArticleResource($article),
            'sections' => SectionResource::collection($sections),
            'tags' => TagResource::collection($tags),
            'related_articles' => $allArticles,
        ]);
    }

    /**
     * Обновить статью
     *
     * @param ArticleRequest $request
     * @param string $id
     * @return RedirectResponse
     */
    public function update(ArticleRequest $request, string $id): RedirectResponse
    {
        $article = Article::findOrFail($id);
        $data = $request->validated();
        $imagesData = $data['images'] ?? [];
        unset($data['images']);

        // Удаление изображений
        if ($request->has('deletedImages')) {
            $this->deleteImages($request->deletedImages);
        }

        // Обновляем данные статьи
        $article->update($data);

        // Синхронизация рубрик и тегов
        $sectionIds = $request->has('sections')
            ? Section::whereIn('title', array_column($data['sections'], 'title'))->pluck('id')->toArray()
            : [];
        $article->sections()->sync($sectionIds);

        $tagIds = $request->has('tags')
            ? Tag::whereIn('name', array_column($data['tags'], 'name'))->pluck('id')->toArray()
            : [];
        $article->tags()->sync($tagIds);

        // Связанные статьи
        $relatedIds = $request->has('related_articles')
            ? Article::whereIn('title', array_column($data['related_articles'], 'title'))->pluck('id')->toArray()
            : [];
        $article->relatedArticles()->sync($relatedIds);

        $imageIds = [];

        // Обработка изображений
        foreach ($imagesData as $imageData) {
            if (!empty($imageData['id'])) {
                $image = ArticleImage::find($imageData['id']);
                if ($image) {
                    $image->update([
                        'order' => $imageData['order'] ?? 0,
                        'alt' => $imageData['alt'] ?? '',
                        'caption' => $imageData['caption'] ?? '',
                    ]);
                    $imageIds[] = $image->id;
                }
            } else {
                // Новое изображение
                $image = ArticleImage::create([
                    'order' => $imageData['order'] ?? 0,
                    'alt' => $imageData['alt'] ?? '',
                    'caption' => $imageData['caption'] ?? '',
                ]);
                $image->addMedia($imageData['file'])->toMediaCollection('images');
                $imageIds[] = $image->id;
            }
        }

        // Синхронизируем связи изображений статьи
        $article->images()->sync($imageIds);

        return redirect()->route('articles.index')->with('success', 'Статья успешно обновлена.');
    }

    /**
     * Удаление статьи вместе с изображениями
     *
     * @param string $id
     * @return RedirectResponse
     */
    public function destroy(string $id): RedirectResponse
    {
        $article = Article::with('images')->findOrFail($id);

        foreach ($article->images as $image) {
            $image->clearMediaCollection('images');
            $image->delete();
        }

        $article->delete();

        Log::info('Статья удалена с ID: ' . $id);

        return back()->with('success', 'Статья и связанные изображения удалены.');
    }

    /**
     * Массовые действия над Статьями
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function bulkDestroy(Request $request): JsonResponse
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

        return response()->json(['success' => true, 'reload' => true]);
    }

    /**
     * Включение Статьи в правом сайдбаре
     *
     * @param Request $request
     * @param $id
     * @return JsonResponse
     */
    public function updateLeft(Request $request, $id): JsonResponse
    {
        $validated = $request->validate([
            'left' => 'required|boolean',
        ]);

        $article = Article::findOrFail($id);
        $article->left = $validated['left'];
        $article->save();

        Log::info("Обновлено включение статьи в левом сайдбаре с ID: $id с данными: ", $validated);

        return response()->json(['success' => true, 'reload' => true]);
    }

    /**
     * Включение Главными
     *
     * @param Request $request
     * @param $id
     * @return JsonResponse
     */
    public function updateMain(Request $request, $id): JsonResponse
    {
        $validated = $request->validate([
            'main' => 'required|boolean',
        ]);

        $article = Article::findOrFail($id);
        $article->main = $validated['main'];
        $article->save();

        Log::info("Обновлено включение основной статьи с ID: $id с данными: ", $validated);

        return response()->json(['success' => true, 'reload' => true]);
    }

    /**
     * Включение Статьи в правом сайдбаре
     *
     * @param Request $request
     * @param $id
     * @return JsonResponse
     */
    public function updateRight(Request $request, $id): JsonResponse
    {
        $validated = $request->validate([
            'right' => 'required|boolean',
        ]);

        $article = Article::findOrFail($id);
        $article->right = $validated['right'];
        $article->save();

        Log::info("Обновлено включение статьи в правом сайдбаре с ID: $id с данными: ", $validated);

        return response()->json(['success' => true, 'reload' => true]);
    }

    /**
     * Обновление активности Статьи
     *
     * @param Request $request
     * @param $id
     * @return JsonResponse
     */
    public function updateActivity(Request $request, $id): JsonResponse
    {
        $validated = $request->validate([
            'activity' => 'required|boolean',
        ]);

        $article = Article::findOrFail($id);
        $article->activity = $validated['activity'];
        $article->save();

        Log::info("Обновлена активность статьи с ID: $id с данными: ", $validated);

        return response()->json(['success' => true, 'reload' => true]);
    }

    /**
     * Сортировка Статей
     *
     * @param Request $request
     * @param $id
     * @return JsonResponse
     */
    public function updateSort(Request $request, $id): JsonResponse
    {
        $validated = $request->validate([
            'sort' => 'required|integer',
        ]);

        $article = Article::findOrFail($id);
        $article->sort = $validated['sort'];
        $article->save();

        Log::info("Обновлена сортировка статьи с ID: $id с данными: ", $validated);

        return response()->json(['success' => true]);
    }

    /**
     * Клонирование Статьи
     *
     * @param Request $request
     * @param $id
     * @return JsonResponse
     */
    public function clone(Request $request, $id): JsonResponse
    {
        $article = Article::findOrFail($id);
        $clonedArticle = $article->replicate(['title', 'url']);
        $clonedArticle->title = $article->title . ' 2';
        $clonedArticle->url = $article->url . '-2';
        $clonedArticle->save();

        $clonedArticle->sections()->sync($article->sections->pluck('id'));
        $clonedArticle->tags()->sync($article->tags->pluck('id'));

        foreach ($article->images as $image) {
            $clonedImage = $image->replicate();
            $clonedImage->save();

            $originalMedia = $image->getFirstMedia('images');
            if ($originalMedia) {
                $clonedImage->addMedia($originalMedia->getPath())->toMediaCollection('images');
            }

            $clonedArticle->images()->attach($clonedImage->id);
        }

        Log::info('Статья клонирована: ', $clonedArticle->toArray());

        return response()->json(['success' => true, 'reload' => true]);
    }

    /**
     * Удаление изображений
     *
     * @param array $imageIds
     * @return void
     */
    private function deleteImages(array $imageIds): void
    {
        $imagesToDelete = ArticleImage::whereIn('id', $imageIds)->get();

        foreach ($imagesToDelete as $image) {
            $image->clearMediaCollection('images'); // удаляем медиафайл из хранилища
            $image->delete();
        }

        Log::info('Удалены изображения: ', ['image_ids' => $imageIds]);
    }

}
