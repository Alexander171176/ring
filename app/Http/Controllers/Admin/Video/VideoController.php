<?php

namespace App\Http\Controllers\Admin\Video;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Video\VideoRequest;
use App\Http\Resources\Admin\Article\ArticleResource;
use App\Http\Resources\Admin\Section\SectionResource;
use App\Http\Resources\Admin\Video\VideoImageResource;
use App\Http\Resources\Admin\Video\VideoResource;
use App\Models\Admin\Article\Article;
use App\Models\Admin\Section\Section;
use App\Models\Admin\Video\Video;
use App\Models\Admin\Video\VideoImage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class VideoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): Response
    {
        $videos = Video::with(['sections', 'articles', 'images'])->get();
        $videosCount = Video::count();

        // Получаем значение параметра из конфигурации (оно загружается через AppServiceProvider)
        $adminCountVideos = config('site_settings.AdminCountVideos', 10);
        $adminSortVideos  = config('site_settings.AdminSortVideos', 'idDesc');

        return Inertia::render('Admin/Videos/Index', [
            'videos' => VideoResource::collection($videos),
            'videosCount' => $videosCount,
            'adminCountVideos' => (int)$adminCountVideos,
            'adminSortVideos' => $adminSortVideos,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): Response
    {
        // Загрузка рубрик
        $sections = Section::all();

        // Загрузка постов
        $articles = Article::all();

        // Загрузка изображений
        $images = VideoImage::all();

        // Загрузка всех статей для выбора в рекомендованных (или нужный поднабор)
        $allVideos = Video::select('id', 'title')->get();

        return Inertia::render('Admin/Videos/Create', [
            'sections' => SectionResource::collection($sections),
            'articles' => ArticleResource::collection($articles),
            'images' => VideoImageResource::collection($images),
            'related_videos' => $allVideos,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(VideoRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $imagesData = $data['images'] ?? [];
        $videoFile = $request->file('video_file'); // Получаем файл отдельно

        // Удаляем данные, не являющиеся колонками таблицы videos
        unset(
            $data['images'],
            $data['sections'],
            $data['articles'],
            $data['related_videos'],
            $data['video_file'] // Удаляем ключ файла из массива данных для создания
        );

        // Если тип источника 'local', не сохраняем video_url
        if ($data['source_type'] === 'local') {
            unset($data['video_url']);
            // Можно обнулить external_video_id, если нужно
            $data['external_video_id'] = null;
        } else {
            // Если тип не local, обнуляем video_url (если вы решили его не использовать для code/external)
            // $data['video_url'] = null; // Раскомментируйте, если нужно
        }


        // Создаем видео
        $video = Video::create($data);

        // --- НОВАЯ ЛОГИКА ЗАГРУЗКИ ЛОКАЛЬНОГО ВИДЕО ---
        if ($data['source_type'] === 'local' && $videoFile && $videoFile->isValid()) {
            try {
                $video->addMedia($videoFile)
                    ->toMediaCollection('videos'); // Используем коллекцию 'videos'
            } catch (\Exception $e) {
                // Обработка ошибки загрузки Spatie (логирование, сообщение пользователю)
                // Возможно, стоит удалить созданное видео, если файл обязателен
                $video->delete(); // Откатываем создание видео
                return back()->withInput()->withErrors(['video_file' => 'Не удалось загрузить видеофайл: ' . $e->getMessage()]);
            }
        }
        // --- КОНЕЦ НОВОЙ ЛОГИКИ ---

        // Синхронизация рубрик
        if ($request->has('sections') && !empty($request->input('sections'))) {
            $sectionIds = Section::whereIn('title', array_column($request->input('sections'), 'title'))->pluck('id')->toArray();
            $video->sections()->sync($sectionIds);
        } else {
            $video->sections()->detach(); // Отсоединяем все, если массив пуст
        }

        // Синхронизация статей
        if ($request->has('articles') && !empty($request->input('articles'))) {
            $articleIds = Article::whereIn('title', array_column($request->input('articles'), 'title'))->pluck('id')->toArray();
            $video->articles()->sync($articleIds);
        } else {
            $video->articles()->detach();
        }

        // Связанные видео
        if ($request->has('related_videos') && !empty($request->input('related_videos'))) {
            $relatedIds = Video::whereIn('title', array_column($request->input('related_videos'), 'title'))
                ->where('id', '<>', $video->id) // Исключаем само себя
                ->pluck('id')->toArray();
            $video->relatedVideos()->sync($relatedIds);
        } else {
            $video->relatedVideos()->detach();
        }

        // Обработка изображений превью через библиотеку spatie
        $imageIds = []; // Массив для хранения ID обработанных изображений
        foreach ($imagesData as $imageData) {
            $image = VideoImage::create([
                'order' => $imageData['order'] ?? 0,
                'alt' => $imageData['alt'] ?? '',
                'caption' => $imageData['caption'] ?? '',
            ]);

            // Добавляем медиа, только если есть файл
            if (isset($imageData['file']) && $imageData['file'] instanceof \Illuminate\Http\UploadedFile) {
                try {
                    $image->addMedia($imageData['file'])->toMediaCollection('images');
                } catch (\Exception $e) {
                    // Обработка ошибки загрузки превью
                    $image->delete(); // Удаляем созданную запись VideoImage
                    // Можно добавить лог или сообщение об ошибке
                    continue; // Пропускаем это изображение
                }
            }
            $imageIds[] = $image->id; // Добавляем ID в массив
        }
        // Синхронизируем только успешно созданные/обработанные изображения
        $video->images()->sync($imageIds);


        return redirect()->route('videos.index')->with('success', 'Видео успешно создано.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id): Response
    {
        // Находим видео с секциями, статьями, изображениями и связанными видео
        $video = Video::with(['sections', 'articles', 'images', 'relatedVideos'])->findOrFail($id);

        // Получаем все рубрики
        $sections = Section::all();

        // Получаем все статьи
        $articles = Article::all();

        // Загружаем все статьи для мультиселекта (исключая текущую)
        $allVideos = Video::where('id', '<>', $video->id)->select('id', 'title')->get();

        return Inertia::render('Admin/Videos/Edit', [
            'video' => new VideoResource($video),
            'sections' => SectionResource::collection($sections),
            'articles' => ArticleResource::collection($articles),
            'related_videos' => $allVideos,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(VideoRequest $request, string $id): RedirectResponse
    {
        $video = Video::findOrFail($id);
        $data = $request->validated();
        $imagesData = $data['images'] ?? [];
        $videoFile = $request->file('video_file'); // Получаем новый файл

        // Удаление старых изображений превью
        if ($request->has('deletedImages')) {
            $this->deleteImages($request->deletedImages);
        }

        // Удаляем данные, не являющиеся прямыми колонками таблицы videos
        unset(
            $data['images'],
            $data['deletedImages'], // Удаляем и это
            $data['sections'],
            $data['articles'],
            $data['related_videos'],
            $data['video_file']
        );

        // Если тип источника меняется на 'local' или остается 'local'
        if ($data['source_type'] === 'local') {
            unset($data['video_url']); // Не обновляем video_url
            // Если пришел новый файл, удаляем старый медиафайл (если он был)
            if ($videoFile && $videoFile->isValid()) {
                $video->clearMediaCollection('videos'); // Удаляем старое видео из коллекции
            }
            // Можно обнулить external_video_id
            $data['external_video_id'] = null;
        } else {
            // Если тип НЕ 'local', удаляем старый локальный медиафайл, если он был
            $video->clearMediaCollection('videos');
            // $data['video_url'] = null; // Можно обнулить URL, если не используется
        }

        // Обновляем основные данные видео
        $video->update($data);

        // --- ЗАГРУЗКА НОВОГО ЛОКАЛЬНОГО ВИДЕО ПРИ ОБНОВЛЕНИИ ---
        if ($data['source_type'] === 'local' && $videoFile && $videoFile->isValid()) {
            try {
                $video->addMedia($videoFile)
                    ->toMediaCollection('videos');
            } catch (\Exception $e) {
                // Обработка ошибки загрузки при обновлении
                return back()->withInput()->withErrors(['video_file' => 'Не удалось загрузить новый видеофайл: ' . $e->getMessage()]);
            }
        }
        // --- КОНЕЦ ЛОГИКИ ЗАГРУЗКИ ПРИ ОБНОВЛЕНИИ ---


        // Синхронизация секций
        $sectionIds = ($request->has('sections') && !empty($request->input('sections')))
            ? Section::whereIn('title', array_column($request->input('sections'), 'title'))->pluck('id')->toArray()
            : [];
        $video->sections()->sync($sectionIds);

        // Синхронизация статей
        $articleIds = ($request->has('articles') && !empty($request->input('articles')))
            ? Article::whereIn('title', array_column($request->input('articles'), 'title'))->pluck('id')->toArray()
            : [];
        $video->articles()->sync($articleIds);

        // Связанные видео
        $relatedIds = ($request->has('related_videos') && !empty($request->input('related_videos')))
            ? Video::whereIn('title', array_column($request->input('related_videos'), 'title'))
                ->where('id', '<>', $video->id) // Исключаем само себя
                ->pluck('id')->toArray()
            : [];
        $video->relatedVideos()->sync($relatedIds);


        // Обработка изображений превью при обновлении
        $currentImageIds = []; // ID изображений, которые должны остаться
        foreach ($imagesData as $imageData) {
            $image = null;
            if (!empty($imageData['id'])) {
                // Обновляем существующее изображение
                $image = VideoImage::find($imageData['id']);
                if ($image) {
                    $image->update([
                        'order' => $imageData['order'] ?? $image->order, // Сохраняем порядок, если не передан
                        'alt' => $imageData['alt'] ?? '',
                        'caption' => $imageData['caption'] ?? '',
                    ]);
                    // Обновляем медиафайл, только если пришел новый файл для СУЩЕСТВУЮЩЕГО VideoImage
                    if (isset($imageData['file']) && $imageData['file'] instanceof \Illuminate\Http\UploadedFile) {
                        try {
                            $image->clearMediaCollection('images'); // Удаляем старый файл
                            $image->addMedia($imageData['file'])->toMediaCollection('images');
                        } catch (\Exception $e) {
                            // Ошибка обновления файла превью
                            // Можно пропустить или вернуть ошибку
                        }
                    }
                }
            } elseif (isset($imageData['file']) && $imageData['file'] instanceof \Illuminate\Http\UploadedFile) {
                // Создаем новое изображение
                $image = VideoImage::create([
                    'order' => $imageData['order'] ?? 0,
                    'alt' => $imageData['alt'] ?? '',
                    'caption' => $imageData['caption'] ?? '',
                ]);
                try {
                    $image->addMedia($imageData['file'])->toMediaCollection('images');
                } catch (\Exception $e) {
                    $image->delete(); // Удаляем запись, если файл не загрузился
                    $image = null; // Сбрасываем, чтобы не добавить ID
                    // Обработка ошибки загрузки превью
                }
            }

            if ($image) {
                $currentImageIds[] = $image->id; // Добавляем ID в массив
            }
        }
        // Синхронизируем только существующие и успешно добавленные изображения
        $video->images()->sync($currentImageIds);


        return redirect()->route('videos.index')->with('success', 'Видео успешно обновлено.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id): RedirectResponse
    {
        $video = Video::with('images')->findOrFail($id);

        foreach ($video->images as $image) {
            $image->clearMediaCollection('images');
            $image->delete();
        }

        $video->delete();

        //Log::info('Видео удалено с ID: ' . $id);

        return back()->with('success', 'Видео и связанные изображения удалены.');
    }

    /**
     * Массовые действия над Видео
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function bulkDestroy(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:videos,id',
        ]);

        $videoIds = $validated['ids'];

        Video::whereIn('id', $videoIds)->each(function ($video) {
            $video->delete();
        });

        //Log::info('Видео удалены: ', $videoIds);

        return response()->json(['success' => true, 'reload' => true]);
    }

    /**
     * Включение Видео в правом сайдбаре
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

        $video = Video::findOrFail($id);
        $video->left = $validated['left'];
        $video->save();

        //Log::info("Обновлено включение видео в левом сайдбаре с ID: $id с данными: ", $validated);

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

        $video = Video::findOrFail($id);
        $video->main = $validated['main'];
        $video->save();

        //Log::info("Обновлено включение основной видео с ID: $id с данными: ", $validated);

        return response()->json(['success' => true, 'reload' => true]);
    }

    /**
     * Включение Видео в правом сайдбаре
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

        $video = Video::findOrFail($id);
        $video->right = $validated['right'];
        $video->save();

        //Log::info("Обновлено включение видео в правом сайдбаре с ID: $id с данными: ", $validated);

        return response()->json(['success' => true, 'reload' => true]);
    }

    /**
     * Обновление активности Видео
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

        $video = Video::findOrFail($id);
        $video->activity = $validated['activity'];
        $video->save();

        //Log::info("Обновлена активность видео с ID: $id с данными: ", $validated);

        return response()->json(['success' => true, 'reload' => true]);
    }

    /**
     * Сортировка Видео
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

        $video = Video::findOrFail($id);
        $video->sort = $validated['sort'];
        $video->save();

        //Log::info("Обновлена сортировка видео с ID: $id с данными: ", $validated);

        return response()->json(['success' => true]);
    }

    /**
     * Удаление изображений
     *
     * @param array $imageIds
     * @return void
     */
    private function deleteImages(array $imageIds): void
    {
        $imagesToDelete = VideoImage::whereIn('id', $imageIds)->get();

        foreach ($imagesToDelete as $image) {
            $image->clearMediaCollection('images'); // удаляем медиафайл из хранилища
            $image->delete();
        }

        //Log::info('Удалены изображения: ', ['image_ids' => $imageIds]);
    }
}
