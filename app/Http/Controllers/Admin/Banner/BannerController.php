<?php

namespace App\Http\Controllers\Admin\Banner;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Banner\BannerRequest;
use App\Http\Resources\Admin\Banner\BannerImageResource;
use App\Http\Resources\Admin\Banner\BannerResource;
use App\Http\Resources\Admin\Section\SectionResource;
use App\Models\Admin\Banner\Banner;
use App\Models\Admin\Banner\BannerImage;
use App\Models\Admin\Section\Section;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response;

class BannerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): Response
    {
        $banners = Banner::with(['sections', 'images'])->get();
        $bannersCount = Banner::count();

        return Inertia::render('Admin/Banners/Index', [
            'banners' => BannerResource::collection($banners),
            'bannersCount' => $bannersCount,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): Response
    {
        // Загрузка рубрик
        $sections = Section::all();

        // Загрузка изображений
        $images = BannerImage::all();

        return Inertia::render('Admin/Banners/Create', [
            'sections' => SectionResource::collection($sections),
            'images' => BannerImageResource::collection($images),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(BannerRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $imagesData = $data['images'] ?? [];
        unset($data['images']);

        // Создаем статью
        $banner = Banner::create($data);

        // Синхронизация баннера и секций
        if (!empty($data['sections'])) {
            $sectionIds = Section::whereIn('title', array_column($data['sections'], 'title'))->pluck('id')->toArray();
            $banner->sections()->sync($sectionIds);
        }

        // Обработка изображений через библиотеку spatie
        foreach ($imagesData as $imageData) {
            $image = BannerImage::create([
                'order' => $imageData['order'] ?? 0,
                'alt' => $imageData['alt'] ?? '',
                'caption' => $imageData['caption'] ?? '',
            ]);

            if (!empty($imageData['file'])) {
                $image->addMedia($imageData['file'])->toMediaCollection('images');
            }

            $banner->images()->attach($image->id);
        }

        return redirect()->route('banners.index')->with('success', 'Баннер успешно создан.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id): Response
    {
        // Находим баннер с рубриками и изображениями
        $banner = Banner::with(['sections', 'images'])->findOrFail($id);

        // Получаем все рубрики
        $sections = Section::all();

        return Inertia::render('Admin/Banners/Edit', [
            'banner' => new BannerResource($banner),
            'sections' => SectionResource::collection($sections),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(BannerRequest $request, string $id): RedirectResponse
    {
        $banner = Banner::findOrFail($id);
        $data = $request->validated();
        $imagesData = $data['images'] ?? [];
        unset($data['images']);

        // Удаление изображений
        if ($request->has('deletedImages')) {
            $this->deleteImages($request->deletedImages);
        }

        // Обновляем данные статьи
        $banner->update($data);

        // Синхронизация рубрик и тегов
        $sectionIds = $request->has('sections')
            ? Section::whereIn('title', array_column($data['sections'], 'title'))->pluck('id')->toArray()
            : [];
        $banner->sections()->sync($sectionIds);

        $imageIds = [];

        // Обработка изображений
        foreach ($imagesData as $imageData) {
            if (!empty($imageData['id'])) {
                $image = BannerImage::find($imageData['id']);
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
                $image = BannerImage::create([
                    'order' => $imageData['order'] ?? 0,
                    'alt' => $imageData['alt'] ?? '',
                    'caption' => $imageData['caption'] ?? '',
                ]);
                $image->addMedia($imageData['file'])->toMediaCollection('images');
                $imageIds[] = $image->id;
            }
        }

        // Синхронизируем связи изображений баннера
        $banner->images()->sync($imageIds);

        return redirect()->route('banners.index')->with('success', 'Баннер успешно обновлен.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id): RedirectResponse
    {
        $banner = Banner::with('images')->findOrFail($id);

        foreach ($banner->images as $image) {
            $image->clearMediaCollection('images');
            $image->delete();
        }

        $banner->delete();

        Log::info('Баннер удален с ID: ' . $id);

        return back()->with('success', 'Баннер и связанные изображения удалены.');
    }

    /**
     * Массовые действия над Баннерами
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function bulkDestroy(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:banners,id',
        ]);

        $bannerIds = $validated['ids'];

        Banner::whereIn('id', $bannerIds)->each(function ($banner) {
            $banner->delete();
        });

        Log::info('Баннеры удалены: ', $bannerIds);

        return response()->json(['success' => true, 'reload' => true]);
    }

    /**
     * Включение Баннера в правом сайдбаре
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

        $banner = Banner::findOrFail($id);
        $banner->left = $validated['left'];
        $banner->save();

        Log::info("Обновлено включение баннера в левом сайдбаре с ID: $id с данными: ", $validated);

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

        $banner = Banner::findOrFail($id);
        $banner->right = $validated['right'];
        $banner->save();

        Log::info("Обновлено включение баннера в правом сайдбаре с ID: $id с данными: ", $validated);

        return response()->json(['success' => true, 'reload' => true]);
    }

    /**
     * Обновление активности Баннера
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

        $banner = Banner::findOrFail($id);
        $banner->activity = $validated['activity'];
        $banner->save();

        Log::info("Обновлена активность баннера с ID: $id с данными: ", $validated);

        return response()->json(['success' => true, 'reload' => true]);
    }

    /**
     * Сортировка Баннеров
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

        $banner = Banner::findOrFail($id);
        $banner->sort = $validated['sort'];
        $banner->save();

        Log::info("Обновлена сортировка банера с ID: $id с данными: ", $validated);

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
        $imagesToDelete = BannerImage::whereIn('id', $imageIds)->get();

        foreach ($imagesToDelete as $image) {
            $image->clearMediaCollection('images'); // удаляем медиафайл из хранилища
            $image->delete();
        }

        Log::info('Удалены изображения: ', ['image_ids' => $imageIds]);
    }

}
