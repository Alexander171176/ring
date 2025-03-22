<?php

namespace App\Http\Controllers\Admin\Article;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Article\ArticleRequest;
use App\Http\Resources\Admin\Article\ArticleImageResource;
use App\Http\Resources\Admin\Article\ArticleResource;
use App\Http\Resources\Admin\Article\TagResource;
use App\Http\Resources\Admin\Section\SectionResource;
use App\Models\Admin\Article\Article;
use App\Models\Admin\Article\ArticleImage;
use App\Models\Admin\Article\Tag;
use App\Models\Admin\Section\Section;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
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

        return Inertia::render('Admin/Articles/Create', [
            'sections' => SectionResource::collection($sections),
            'tags' => TagResource::collection($tags),
            'images' => ArticleImageResource::collection($images),
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

        // Найти рубрики по title
        $sectionIds = [];
        if ($request->has('sections')) {
            $sectionTitles = array_column($request->input('sections'), 'title');
            $sectionIds = Section::whereIn('title', $sectionTitles)->pluck('id')->toArray();
        }

        // Найти теги по name
        $tagIds = [];
        if ($request->has('tags')) {
            $tagNames = array_column($request->input('tags'), 'name');
            $tagIds = Tag::whereIn('name', $tagNames)->pluck('id')->toArray();
        }

        // Создаем статью
        $article = Article::create($data);

        // Привязываем рубрики
        if ($sectionIds) {
            $article->sections()->sync($sectionIds);
        }

        // Привязываем теги
        if ($tagIds) {
            $article->tags()->sync($tagIds);
        }

        // Обрабатываем изображения (новые файлы + обновление alt/caption)
        if ($request->has('images')) {
            foreach ($data['images'] as $imageData) {
                if (isset($imageData['file']) && $imageData['file'] instanceof UploadedFile) {

                    // Загружаем новое изображение
                    $path = $imageData['file']->store('article_images', 'public');
                    $image = ArticleImage::create([
                        'path' => $path,
                        'order' => $imageData['order'] ?? 0,
                        'alt' => $imageData['alt'] ?? '',
                        'caption' => $imageData['caption'] ?? '',
                    ]);
                    $article->images()->attach($image->id);
                } elseif (isset($imageData['id'])) {

                    // Обновляем alt и caption для существующего изображения
                    $existingImage = ArticleImage::find($imageData['id']);
                    if ($existingImage) {
                        $existingImage->update([
                            'order' => $imageData['order'] ?? 0,
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
     * @return Response
     */
    public function edit(string $id): Response
    {
        // Находим статью с рубриками, тегами и изображениями
        $article = Article::with(['sections', 'tags', 'images'])->findOrFail($id);

        // Получаем все рубрики
        $sections = Section::all();

        // Получаем все теги
        $tags = Tag::all();

        return Inertia::render('Admin/Articles/Edit', [
            'article' => new ArticleResource($article),
            'sections' => SectionResource::collection($sections),
            'tags' => TagResource::collection($tags),
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

        // Обработка удаления изображений (удалённых с клиента)
        if ($request->has('deletedImages') && is_array($request->deletedImages)) {
            $this->deleteImages($request->deletedImages);
        }

        // Обновляем данные статьи
        $article->update($data);

        // Обновляем связи рубрик
        $sectionIds = $request->has('sections')
            ? Section::whereIn('title', array_column($request->input('sections'), 'title'))->pluck('id')->toArray()
            : [];
        $article->sections()->sync($sectionIds);

        // Обновляем связи тегов
        $tagIds = $request->has('tags')
            ? Tag::whereIn('name', array_column($request->input('tags'), 'name'))->pluck('id')->toArray()
            : [];
        $article->tags()->sync($tagIds);

        // Обрабатываем изображения: новые и обновление существующих
        if (!empty($imagesData)) {
            $imageIds = [];

            foreach ($imagesData as $imageData) {
                if (isset($imageData['file']) && $imageData['file'] instanceof UploadedFile) {

                    // Загружаем новое изображение
                    $path = $imageData['file']->store('article_images', 'public');
                    $image = ArticleImage::create([
                        'path' => $path,
                        'order' => $imageData['order'] ?? 0,
                        'alt' => $imageData['alt'] ?? '',
                        'caption' => $imageData['caption'] ?? '',
                    ]);
                    $imageIds[] = $image->id;
                } elseif (isset($imageData['id'])) {

                    // Обновляем alt и caption для существующего изображения
                    $existingImage = ArticleImage::find($imageData['id']);
                    if ($existingImage) {
                        $existingImage->update([
                            'order' => $imageData['order'] ?? 0,
                            'alt' => $imageData['alt'] ?? '',
                            'caption' => $imageData['caption'] ?? '',
                        ]);
                        $imageIds[] = $existingImage->id;
                    } else {
                        Log::warning('Существующее изображение не найдено', ['image_id' => $imageData['id']]);
                    }
                }
            }

            // Синхронизируем связи изображений статьи с актуальным списком
            $article->images()->sync($imageIds);
        } else {
            Log::info('В запросе нет изображений');
        }

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
        $article = Article::findOrFail($id);

        // Удаляем связанные изображения
        foreach ($article->images as $image) {
            $imagePath = $image->path;
            if (Storage::disk('public')->exists($imagePath)) {
                Storage::disk('public')->delete($imagePath);
                Log::info("Файл успешно удалён: {$imagePath}");
            } else {
                Log::warning("Файл не найден: {$imagePath}");
            }
            $image->delete();
        }

        // Удаляем статью
        $article->delete();

        Log::info('Статья удалена: ', $article->toArray());

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

        Log::info("Обновлено включение основным статьи с ID: $id с данными: ", $validated);

        return response()->json(['success' => true, 'reload' => true]);
    }

    /**
     * Включение Статьи в сайдбаре
     *
     * @param Request $request
     * @param $id
     * @return JsonResponse
     */
    public function updateSidebar(Request $request, $id): JsonResponse
    {
        $validated = $request->validate([
            'sidebar' => 'required|boolean',
        ]);

        $article = Article::findOrFail($id);
        $article->sidebar = $validated['sidebar'];
        $article->save();

        Log::info("Обновлено включение статьи в сайдбар с ID: $id с данными: ", $validated);

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

        // Клонирование статьи
        $clonedArticle = $article->replicate();
        $clonedArticle->title = $article->title . ' 2';
        $clonedArticle->url = $article->url . '-2';
        $clonedArticle->save();

        // Копируем рубрики
        $sectionIds = $article->sections->pluck('id')->toArray();
        if ($sectionIds) {
            $clonedArticle->sections()->sync($sectionIds);
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
            if ($image->path && Storage::disk('public')->exists($image->path)) {
                Storage::disk('public')->delete($image->path);
                Log::info("Файл успешно удалён: {$image->path}");
            } else {
                Log::warning("Файл не найден: {$image->path}");
            }
            $image->delete();
        }

        Log::info('Удалены изображения: ', ['image_ids' => $imageIds]);
    }
}
