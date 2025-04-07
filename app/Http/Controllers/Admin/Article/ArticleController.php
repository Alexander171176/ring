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
use Illuminate\Support\Facades\DB; // <--- Используем DB фасад для транзакций
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response;
use Throwable; // <--- Импортируем Throwable для обработки исключений

class ArticleController extends Controller
{
    /**
     * Все статьи
     *
     * @return Response
     */
    public function index(): Response
    {
        // Используем пагинацию вместо ->get() для больших списков
        $adminCountArticles = config('site_settings.AdminCountArticles', 15); // Стандартно 15, если нет настройки
        $adminSortArticles = config('site_settings.AdminSortArticles', 'idDesc');

        // Логика сортировки
        $sortField = 'id';
        $sortDirection = 'desc';
        if ($adminSortArticles === 'sortAsc') {
            $sortField = 'sort';
            $sortDirection = 'asc';
        } // Добавьте другие варианты сортировки, если нужно

        $articles = Article::with(['sections', 'tags', 'images'])
            ->orderBy($sortField, $sortDirection)
            ->paginate($adminCountArticles); // Используем paginate()

        $articlesCount = Article::count(); // Можно оставить для общего счетчика

        return Inertia::render('Admin/Articles/Index', [
            'articles' => ArticleResource::collection($articles), // Пагинированный ресурс
            'articlesCount' => $articlesCount,
            'adminCountArticles' => $adminCountArticles, // Передаем для отображения
            'adminSortArticles' => $adminSortArticles,
        ]);
    }

    /**
     * Страница создать Статью
     *
     * @return Response
     */
    public function create(): Response
    {
        // Загружаем только необходимые поля для селектов
        $sections = Section::select('id', 'title')->get();
        $tags = Tag::select('id', 'name')->get(); // Предполагаем, что у тега поле 'name'
        $allArticles = Article::select('id', 'title')->get();

        // images здесь не нужны, т.к. они будут загружаться в форме
        // $images = ArticleImage::all(); // УБРАТЬ

        return Inertia::render('Admin/Articles/Create', [
            'sections' => SectionResource::collection($sections),
            'tags' => TagResource::collection($tags),
            // 'images' => ArticleImageResource::collection($images), // УБРАТЬ
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
        $sectionTitles = isset($data['sections']) ? array_column($data['sections'], 'title') : [];
        $tagNames = isset($data['tags']) ? array_column($data['tags'], 'name') : [];
        $relatedTitles = isset($data['related_articles']) ? array_column($data['related_articles'], 'title') : [];

        // Удаляем связи и изображения из основных данных
        unset($data['images'], $data['sections'], $data['tags'], $data['related_articles']);

        try {
            // Начинаем транзакцию
            DB::beginTransaction();

            // Создаем статью
            $article = Article::create($data);

            // Получаем ID для связей после валидации
            $sectionIds = !empty($sectionTitles) ? Section::whereIn('title', $sectionTitles)->pluck('id')->toArray() : [];
            $tagIds = !empty($tagNames) ? Tag::whereIn('name', $tagNames)->pluck('id')->toArray() : [];
            $relatedIds = !empty($relatedTitles) ? Article::whereIn('title', $relatedTitles)->where('id', '<>', $article->id)->pluck('id')->toArray() : [];

            // Синхронизация связей
            $article->sections()->sync($sectionIds);
            $article->tags()->sync($tagIds);
            $article->relatedArticles()->sync($relatedIds);

            // Обработка изображений
            $imageSyncData = []; // Массив для данных сводной таблицы [image_id => ['order' => value]]
            foreach ($imagesData as $imageData) {
                // Создаем ArticleImage только если есть файл (т.к. это store)
                if (isset($imageData['file']) && $request->hasFile('images.' . key($imagesData) . '.file')) {
                    $image = ArticleImage::create([
                        'order'   => $imageData['order'] ?? 0, // Используем order из запроса
                        'alt'     => $imageData['alt'] ?? '',
                        'caption' => $imageData['caption'] ?? '',
                    ]);

                    try {
                        $file = $request->file('images.' . key($imagesData) . '.file');
                        if ($file && $file->isValid()) {
                            $image->addMedia($file)->toMediaCollection('images');
                            // Готовим данные для sync со сводной таблицы
                            $imageSyncData[$image->id] = ['order' => $image->order];
                        } else {
                            $image->delete(); // Удаляем, если файл невалидный
                            Log::warning('Невалидный файл изображения при создании статьи', ['imageData' => $imageData]);
                        }
                    } catch (\Exception $e) {
                        $image->delete(); // Удаляем при ошибке Spatie
                        Log::error('Ошибка добавления медиа Spatie при создании статьи: ' . $e->getMessage(), ['imageData' => $imageData]);
                        // Рассмотрите возможность отката всей транзакции или возврата ошибки
                        // DB::rollBack();
                        // return back()->withInput()->withErrors(['images' => 'Ошибка загрузки изображения.']);
                    }
                }
                next($imagesData); // Переход к следующему файлу
            }

            // Синхронизируем изображения с данными для сводной таблицы
            $article->images()->sync($imageSyncData);

            // Если все успешно, подтверждаем транзакцию
            DB::commit();

            return redirect()->route('articles.index')->with('success', 'Статья успешно создана.');

        } catch (Throwable $e) {
            // Откатываем транзакцию в случае любой ошибки
            DB::rollBack();
            Log::error("Ошибка при создании статьи: " . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return back()->withInput()->withErrors(['general' => 'Произошла ошибка при создании статьи. Пожалуйста, попробуйте снова.']);
        }
    }

    /**
     * Страница редактирования Статьи
     *
     * @param string $id
     * @return Response
     */
    public function edit(string $id): Response
    {
        // Загружаем статью со связями и сортируем изображения
        $article = Article::with([
            'sections',
            'tags',
            'images' => fn ($query) => $query->orderBy('pivot_order', 'asc'), // Используем сортировку из связи
            'relatedArticles'
        ])->findOrFail($id);

        // Загружаем только необходимые поля для селектов
        $sections = Section::select('id', 'title')->get();
        $tags = Tag::select('id', 'name')->get();
        $allArticles = Article::where('id', '<>', $article->id)->select('id', 'title')->get();

        return Inertia::render('Admin/Articles/Edit', [
            'article' => new ArticleResource($article), // Используем ArticleResource
            'sections' => SectionResource::collection($sections),
            'tags' => TagResource::collection($tags),
            'related_articles' => $allArticles, // Ресурс здесь не нужен, т.к. это просто список для селекта
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
        $article = Article::findOrFail($id); // Загружаем статью
        $data = $request->validated();
        $imagesData = $data['images'] ?? [];
        $deletedImageIds = $data['deletedImages'] ?? [];

        $sectionTitles = isset($data['sections']) ? array_column($data['sections'], 'title') : [];
        $tagNames = isset($data['tags']) ? array_column($data['tags'], 'name') : [];
        $relatedTitles = isset($data['related_articles']) ? array_column($data['related_articles'], 'title') : [];

        // Удаляем ненужные ключи из $data
        unset($data['images'], $data['deletedImages'], $data['sections'], $data['tags'], $data['related_articles'], $data['_method']);

        try {
            DB::beginTransaction();

            // Удаление изображений, отмеченных для удаления
            if (!empty($deletedImageIds)) {
                // Сначала отсоединяем от статьи, потом удаляем ArticleImage и медиа
                $article->images()->detach($deletedImageIds);
                $this->deleteImages($deletedImageIds); // Используем наш приватный метод
            }

            // Обновляем основные данные статьи
            $article->update($data);

            // Получаем ID для связей
            $sectionIds = !empty($sectionTitles) ? Section::whereIn('title', $sectionTitles)->pluck('id')->toArray() : [];
            $tagIds = !empty($tagNames) ? Tag::whereIn('name', $tagNames)->pluck('id')->toArray() : [];
            $relatedIds = !empty($relatedTitles) ? Article::whereIn('title', $relatedTitles)->pluck('id')->toArray() : []; // При обновлении можно не исключать себя

            // Синхронизация связей
            $article->sections()->sync($sectionIds);
            $article->tags()->sync($tagIds);
            $article->relatedArticles()->sync($relatedIds);

            // Обработка изображений
            $imageSyncData = []; // [image_id => ['order' => value]]
            $imageIndex = 0; // Индекс для доступа к файлам
            foreach ($imagesData as $imageData) {
                $image = null;
                $fileKey = 'images.' . $imageIndex . '.file'; // Ключ файла в запросе

                if (!empty($imageData['id'])) {
                    // Обновляем существующее ArticleImage
                    $image = ArticleImage::find($imageData['id']);
                    if ($image) {
                        $newOrder = $imageData['order'] ?? $image->order; // Обновляем order, если передан
                        $image->update([
                            'order'   => $newOrder, // Обновляем order в самой модели ArticleImage (хотя основное - в pivot)
                            'alt'     => $imageData['alt'] ?? '',
                            'caption' => $imageData['caption'] ?? '',
                        ]);

                        // Если пришел НОВЫЙ файл для СУЩЕСТВУЮЩЕГО image
                        if ($request->hasFile($fileKey)) {
                            try {
                                $file = $request->file($fileKey);
                                if ($file && $file->isValid()) {
                                    $image->clearMediaCollection('images'); // Удаляем старый файл Spatie
                                    $image->addMedia($file)->toMediaCollection('images');
                                } else {
                                    Log::warning('Невалидный файл при обновлении изображения статьи', ['imageData' => $imageData]);
                                }
                            } catch (\Exception $e) {
                                Log::error('Ошибка обновления медиа Spatie при обновлении статьи: ' . $e->getMessage(), ['imageData' => $imageData]);
                                // Возможно, стоит вернуть ошибку
                            }
                        }
                        // Добавляем в данные для sync с order
                        $imageSyncData[$image->id] = ['order' => $newOrder];
                    }
                } elseif ($request->hasFile($fileKey)) {
                    // Создаем новое ArticleImage
                    $image = ArticleImage::create([
                        'order'   => $imageData['order'] ?? 0,
                        'alt'     => $imageData['alt'] ?? '',
                        'caption' => $imageData['caption'] ?? '',
                    ]);
                    try {
                        $file = $request->file($fileKey);
                        if ($file && $file->isValid()) {
                            $image->addMedia($file)->toMediaCollection('images');
                            // Добавляем в данные для sync с order
                            $imageSyncData[$image->id] = ['order' => $image->order];
                        } else {
                            $image->delete(); // Удаляем запись, если файл невалидный
                            Log::warning('Невалидный файл для нового изображения при обновлении статьи', ['imageData' => $imageData]);
                        }
                    } catch (\Exception $e) {
                        $image->delete(); // Удаляем запись при ошибке Spatie
                        Log::error('Ошибка добавления нового медиа Spatie при обновлении статьи: ' . $e->getMessage(), ['imageData' => $imageData]);
                        // Возможно, стоит вернуть ошибку
                    }
                }
                $imageIndex++; // Увеличиваем индекс
            }

            // Синхронизируем изображения со статьей, передавая 'order' для сводной таблицы
            $article->images()->sync($imageSyncData);

            DB::commit(); // Подтверждаем транзакцию

            return redirect()->route('articles.index')->with('success', 'Статья успешно обновлена.');

        } catch (Throwable $e) {
            DB::rollBack(); // Откатываем транзакцию
            Log::error("Ошибка при обновлении статьи ID {$id}: " . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return back()->withInput()->withErrors(['general' => 'Произошла ошибка при обновлении статьи. Пожалуйста, попробуйте снова.']);
        }
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

        try {
            DB::beginTransaction();

            // Используем приватный метод для удаления ArticleImage и медиа
            $this->deleteImages($article->images->pluck('id')->toArray());
            // Отсоединять не нужно, т.к. deleteImages удаляет сами ArticleImage

            // Удаляем статью (связи в сводных таблицах удалятся каскадно, если настроено)
            $article->delete();

            DB::commit();
            Log::info('Статья удалена с ID: ' . $id);
            return back()->with('success', 'Статья и связанные изображения удалены.');

        } catch (Throwable $e) {
            DB::rollBack();
            Log::error("Ошибка при удалении статьи ID {$id}: " . $e->getMessage());
            return back()->withErrors(['general' => 'Произошла ошибка при удалении статьи.']);
        }
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

        try {
            DB::beginTransaction();

            Article::whereIn('id', $articleIds)->each(function (Article $article) {
                // Используем приватный метод для удаления изображений каждой статьи
                $this->deleteImages($article->images()->pluck('id')->toArray());
                // Отсоединять не нужно

                // Удаляем саму статью
                $article->delete();
            });

            DB::commit();
            Log::info('Статьи удалены: ', $articleIds);
            return response()->json(['success' => true, 'reload' => true]);

        } catch (Throwable $e) {
            DB::rollBack();
            Log::error("Ошибка при массовом удалении статей: " . $e->getMessage(), ['ids' => $articleIds]);
            return response()->json(['success' => false, 'message' => 'Ошибка при удалении статей.'], 500);
        }
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

        //Log::info("Обновлено включение статьи в левом сайдбаре с ID: $id с данными: ", $validated);

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

        //Log::info("Обновлено включение основной статьи с ID: $id с данными: ", $validated);

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

        //Log::info("Обновлено включение статьи в правом сайдбаре с ID: $id с данными: ", $validated);

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

        //Log::info("Обновлена активность статьи с ID: $id с данными: ", $validated);

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

        //Log::info("Обновлена сортировка статьи с ID: $id с данными: ", $validated);

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

        //Log::info('Статья клонирована: ', $clonedArticle->toArray());

        return response()->json(['success' => true, 'reload' => true]);
    }


    /**
     * Удаление записей ArticleImage и их медиафайлов.
     * Этот метод теперь вызывается из destroy и bulkDestroy.
     * При update удаление происходит через detach + вызов этого метода.
     *
     * @param array $imageIds
     * @return void
     */
    private function deleteImages(array $imageIds): void
    {
        if (empty($imageIds)) {
            return;
        }
        // Ищем ArticleImage для удаления
        $imagesToDelete = ArticleImage::whereIn('id', $imageIds)->get();

        foreach ($imagesToDelete as $image) {
            $image->clearMediaCollection('images'); // Удаляем медиафайл Spatie
            $image->delete(); // Удаляем запись ArticleImage из базы данных
        }

        Log::info('Удалены записи ArticleImage и их медиа: ', ['image_ids' => $imageIds]);
    }

}
