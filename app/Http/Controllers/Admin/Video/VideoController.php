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
        unset($data['images']);

        // Создаем видео
        $video = Video::create($data);

        // Синхронизация рубрик и тегов
        if (!empty($data['sections'])) {
            $sectionIds = Section::whereIn('title', array_column($data['sections'], 'title'))->pluck('id')->toArray();
            $video->sections()->sync($sectionIds);
        }

        if (!empty($data['articles'])) {
            $articleIds = Article::whereIn('title', array_column($data['articles'], 'title'))->pluck('id')->toArray();
            $video->articles()->sync($articleIds);
        }

        // Связанные статьи
        if (!empty($data['related_videos'])) {
            $relatedIds = Video::whereIn('title', array_column($data['related_videos'], 'title'))
                ->where('id', '<>', $video->id)
                ->pluck('id')->toArray();
            $video->relatedVideos()->sync($relatedIds);
        }

        // Обработка изображений через библиотеку spatie
        foreach ($imagesData as $imageData) {
            $image = VideoImage::create([
                'order' => $imageData['order'] ?? 0,
                'alt' => $imageData['alt'] ?? '',
                'caption' => $imageData['caption'] ?? '',
            ]);

            if (!empty($imageData['file'])) {
                $image->addMedia($imageData['file'])->toMediaCollection('images');
            }

            $video->images()->attach($image->id);
        }

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
        unset($data['images']);

        // Удаление изображений
        if ($request->has('deletedImages')) {
            $this->deleteImages($request->deletedImages);
        }

        // Обновляем данные статьи
        $video->update($data);

        // Синхронизация секций и постов
        $sectionIds = $request->has('sections')
            ? Section::whereIn('title', array_column($data['sections'], 'title'))->pluck('id')->toArray()
            : [];
        $video->sections()->sync($sectionIds);

        $articleIds = $request->has('articles')
            ? Article::whereIn('title', array_column($data['articles'], 'title'))->pluck('id')->toArray()
            : [];
        $video->articles()->sync($articleIds);

        // Связанные видео
        $relatedIds = $request->has('related_videos')
            ? Video::whereIn('title', array_column($data['related_videos'], 'title'))->pluck('id')->toArray()
            : [];
        $video->relatedVideos()->sync($relatedIds);

        $imageIds = [];

        // Обработка изображений
        foreach ($imagesData as $imageData) {
            if (!empty($imageData['id'])) {
                $image = VideoImage::find($imageData['id']);
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
                $image = VideoImage::create([
                    'order' => $imageData['order'] ?? 0,
                    'alt' => $imageData['alt'] ?? '',
                    'caption' => $imageData['caption'] ?? '',
                ]);
                $image->addMedia($imageData['file'])->toMediaCollection('images');
                $imageIds[] = $image->id;
            }
        }

        // Синхронизируем связи изображений статьи
        $video->images()->sync($imageIds);

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
