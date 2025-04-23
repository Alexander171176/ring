<?php

namespace App\Http\Controllers\Admin\Banner;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Banner\BannerRequest; // Используем

// Реквесты для простых действий
use App\Http\Requests\Admin\UpdateActivityRequest;
use App\Http\Requests\Admin\UpdateLeftRequest;
use App\Http\Requests\Admin\UpdateRightRequest;
use App\Http\Requests\Admin\UpdateSortEntityRequest;
use App\Http\Requests\Admin\UpdateSortRequest;

// Ресурсы
use App\Http\Resources\Admin\Banner\BannerResource;
use App\Http\Resources\Admin\Banner\BannerImageResource; // Нужен для edit
use App\Http\Resources\Admin\Section\SectionResource; // Для списка секций
// Модели
use App\Models\Admin\Banner\Banner;
use App\Models\Admin\Banner\BannerImage;
use App\Models\Admin\Section\Section;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request; // Для bulkDestroy
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response;
use Throwable;

/**
 * Контроллер для управления Баннерами в административной панели.
 *
 * Предоставляет CRUD операции, а также дополнительные действия:
 * - Массовое удаление
 * - Обновление активности и сортировки (одиночное и массовое)
 * - Клонирование
 *
 * @version 1.1 (Улучшен с RMB, транзакциями, Form Requests)
 * @author Александр Косолапов <kosolapov1976@gmail.com>
 * @see \App\Models\Admin\Banner\Banner Модель Баннера
 * @see \App\Http\Requests\Admin\Banner\BannerRequest Запрос для создания/обновления
 */
class BannerController extends Controller
{
    /**
     * Отображение списка всех Баннеров.
     * Загружает пагинированный список с сортировкой по настройкам.
     * Передает данные для отображения и настройки пагинации/сортировки.
     * Пагинация и сортировка выполняются на фронтенде.
     *
     * @return Response
     */
    public function index(): Response
    {
        // TODO: Проверка прав $this->authorize('view-banner', Banner::class);

        $adminCountBanners = config('site_settings.AdminCountBanners', 15);
        $adminSortBanners  = config('site_settings.AdminSortBanners', 'idDesc');

        try {
            // Загружаем ВСЕ статьи с количеством секций (или без, если не нужно в таблице)
            $banners = Banner::withCount(['sections', 'images'])
            ->with('images') // Загружаем полные данные, если превью нужны в таблице
            ->get(); // Загружаем ВСЕ

            $bannersCount = $banners->count(); // Считаем из загруженной коллекции

        } catch (Throwable $e) {
            Log::error("Ошибка загрузки баннеров для Index: " . $e->getMessage());
            $banners = collect(); // Пустая коллекция в случае ошибки
            $bannersCount = 0;
            session()->flash('error', 'Не удалось загрузить список баннеров.');
        }

        return Inertia::render('Admin/Banners/Index', [
            'banners' => BannerResource::collection($banners),
            'bannersCount' => $bannersCount,
            'adminCountBanners' => (int)$adminCountBanners,
            'adminSortBanners' => $adminSortBanners,
        ]);
    }

    /**
     * Отображение формы создания нового баннера.
     * Передает список баннеров для выбора.
     *
     * @return Response
     */
    public function create(): Response
    {
        // TODO: Проверка прав $this->authorize('create-banner', Banner::class);

        // Передаем список секций (только нужные поля)
        $sections = Section::select('id', 'title', 'locale')->orderBy('title')->get();

        return Inertia::render('Admin/Banners/Create', [
            'sections' => SectionResource::collection($sections), // Используем ресурс (Shared не было)
        ]);
    }

    /**
     * Создание баннера.
     */
    public function store(BannerRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $imagesData   = $data['images'] ?? [];
        $sectionIds   = collect($data['sections'] ?? [])->pluck('id')->toArray();
        unset($data['images'], $data['sections']);

        try {
            DB::beginTransaction();
            $banner = Banner::create($data);

            // Связи
            $banner->sections()->sync($sectionIds);

            // Обработка изображений
            $imageSyncData = [];
            $imageIndex    = 0;
            foreach ($imagesData as $imageData) {
                $fileKey = "images.{$imageIndex}.file";

                if ($request->hasFile($fileKey)) {
                    // Сначала создаём запись
                    $image = BannerImage::create([
                        'order'   => $imageData['order']   ?? 0,
                        'alt'     => $imageData['alt']     ?? '',
                        'caption' => $imageData['caption'] ?? '',
                    ]);

                    try {
                        $file = $request->file($fileKey);

                        if ($file->isValid()) {
                            $media = $image
                                ->addMedia($file)
                                ->toMediaCollection('images');

                            $imageSyncData[$image->id] = ['order' => $image->order];
                        } else {
                            Log::warning("Недопустимый файл изображения с индексом {$imageIndex} для баннера {$banner->id}", [
                                'fileKey' => $fileKey,
                                'error'   => $file->getErrorMessage(),
                            ]);
                            // Откатили создание BannerImage
                            $image->delete();
                            continue;
                        }
                    } catch (Throwable $e) {
                        Log::error("Ошибка Spatie media-library в статье {$banner->id}, индекс изображения - {$imageIndex}: {$e->getMessage()}", [
                            'trace' => $e->getTraceAsString(),
                        ]);
                        // Откатили создание BannerImage
                        $image->delete();
                        continue;
                    }
                }

                $imageIndex++;
            }

            $banner->images()->sync($imageSyncData);

            DB::commit();
            Log::info('Баннер успешно создан', ['id' => $banner->id, 'title' => $banner->title]);
            return redirect()->route('admin.banners.index')
                ->with('success', 'Баннер успешно создан.');
        } catch (Throwable $e) {
            DB::rollBack();
            Log::error("Ошибка при создании баннера: {$e->getMessage()}", [
                'trace' => $e->getTraceAsString(),
            ]);
            return back()
                ->withInput()
                ->withErrors(['general' => 'Произошла ошибка при создании статьи.']);
        }
    }

    /**
     * Отображение формы редактирования существующего баннера.
     * Использует Route Model Binding для получения модели.
     *
     * @param Banner $banner Модель баннера, найденная по ID из маршрута.
     * @return Response
     */
    public function edit(Banner $banner): Response // Используем RMB
    {
        // TODO: Проверка прав $this->authorize('update-banner', $banner);

        // Загружаем связи с сортировкой изображений
        $banner->load(['sections', 'images' => fn($q) => $q->orderBy('order', 'asc')]);

        // Передаем список всех секций для выбора
        $sections = Section::select('id', 'title', 'locale')->orderBy('title')->get();

        return Inertia::render('Admin/Banners/Edit', [
            'banner' => new BannerResource($banner),
            'sections' => SectionResource::collection($sections),
        ]);
    }

    /**
     * Обновление существующего баннера в базе данных.
     * Использует BannerRequest и Route Model Binding.
     * Синхронизирует связанные изображения, секции если они переданы.
     *
     * @param BannerRequest $request Валидированный запрос.
     * @param Banner $banner Модель баннера для обновления.
     * @return RedirectResponse Редирект на список баннеров с сообщением.
     */
    public function update(BannerRequest $request, Banner $banner): RedirectResponse // Используем RMB
    {
        $data = $request->validated();

        // Извлекаем все данные
        $imagesData       = $data['images'] ?? [];
        $deletedImageIds  = $data['deletedImages'] ?? [];
        $sectionIds       = collect($data['sections'] ?? [])->pluck('id')->toArray();

        // Убираем ненужные ключи из $data
        unset(
            $data['images'],
            $data['deletedImages'],
            $data['sections'],
            $data['_method']
        );

        try {
            DB::beginTransaction();

            // 1) Удаляем выбранные пользователем изображения
            if (!empty($deletedImageIds)) {
                // отвязываем от pivot
                $banner->images()->detach($deletedImageIds);
                // удаляем сами записи и файлы
                $this->deleteImages($deletedImageIds);
            }

            // 2) Обновляем базовые поля статьи
            $banner->update($data);

            // 3) Синхронизация связей
            $banner->sections()->sync($sectionIds);

            // 4) Обработка изображений
            $syncData = [];
            foreach ($imagesData as $index => $imageData) {
                $fileKey = "images.{$index}.file";

                // a) Существующее изображение
                if (!empty($imageData['id'])) {
                    $img = BannerImage::find($imageData['id']);

                    // Если изображение не удаляется
                    if ($img && !in_array($img->id, $deletedImageIds, true)) {
                        // Обновляем order, alt, caption
                        $img->update([
                            'order'   => $imageData['order']   ?? $img->order,
                            'alt'     => $imageData['alt']     ?? $img->alt,
                            'caption' => $imageData['caption'] ?? $img->caption,
                        ]);

                        // Если пришёл новый файл — меняем медиа
                        if ($request->hasFile($fileKey)) {
                            $img->clearMediaCollection('images');
                            $img->addMedia($request->file($fileKey))
                                ->toMediaCollection('images');
                        }

                        // Готовим данные для pivot sync
                        $syncData[$img->id] = ['order' => $img->order];
                    }

                    // b) Новое изображение (нет ID, но есть файл)
                } elseif ($request->hasFile($fileKey)) {
                    $new = BannerImage::create([
                        'order'   => $imageData['order']   ?? 0,
                        'alt'     => $imageData['alt']     ?? '',
                        'caption' => $imageData['caption'] ?? '',
                    ]);

                    // Загружаем файл
                    $new->addMedia($request->file($fileKey))
                        ->toMediaCollection('images');

                    $syncData[$new->id] = ['order' => $new->order];
                }
            }

            // 5) Синхронизируем оставшиеся и новые изображения в pivot
            $banner->images()->sync($syncData);

            DB::commit();

            Log::info('Баннер обновлен: ', ['id' => $banner->id, 'title' => $banner->title]);
            return redirect()
                ->route('admin.banners.index')
                ->with('success', 'Баннер успешно обновлен.');

        } catch (Throwable $e) {
            DB::rollBack();
            Log::error("Ошибка при обновлении баннера ID {$banner->id}: {$e->getMessage()}", [
                'trace' => $e->getTraceAsString(),
            ]);
            return back()
                ->withInput()
                ->withErrors(['general' => 'Произошла ошибка при обновлении статьи.']);
        }
    }

    /**
     * Удаление указанного баннера вместе с изображениями.
     * Использует Route Model Binding. Связи удаляются каскадно.
     *
     * @param Banner $banner Модель баннера для удаления.
     * @return RedirectResponse Редирект на список баннеров с сообщением.
     */
    public function destroy(Banner $banner): RedirectResponse // Используем RMB
    {
        // TODO: Проверка прав $this->authorize('delete-banner', $banner);
        try {
            DB::beginTransaction();
            // Удаляем связанные BannerImage и их медиа, используем приватный метод deleteImages
            $this->deleteImages($banner->images()->pluck('id')->toArray());
            $banner->delete(); // Связи с секциями удалятся каскадно
            DB::commit();
            Log::info('Баннер удален: ID ' . $banner->id);
            return redirect()->route('admin.banners.index')->with('success', 'Баннер и связанные изображения удалены.');
        } catch (Throwable $e) {
            DB::rollBack();
            Log::error("Ошибка при удалении баннера ID {$banner->id}: " . $e->getMessage());
            return back()->withErrors(['general' => 'Произошла ошибка при удалении баннера.']);
        }
    }

    /**
     * Массовое удаление указанных баннеров.
     * Принимает массив ID в теле запроса.
     *
     * @param Request $request Запрос, содержащий массив 'ids'.
     * @return RedirectResponse Редирект назад с сообщением.
     */
    public function bulkDestroy(Request $request): RedirectResponse
    {
        // TODO: Проверка прав $this->authorize('delete-banners');

        $validated = $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'required|integer|exists:banners,id',
        ]);

        $bannerIds = $validated['ids'];
        $count = count($bannerIds); // Получаем количество для сообщения

        try {
            DB::beginTransaction(); // Оставляем транзакцию для массовой операции

            $allImageIds = BannerImage::whereHas('banners', fn($q) => $q->whereIn('banners.id', $bannerIds))
                ->pluck('id')->toArray();
            if (!empty($allImageIds)) {
                DB::table('banner_has_images')->whereIn('banner_id', $bannerIds)->delete();
                $this->deleteImages($allImageIds);
            }

            Banner::whereIn('id', $bannerIds)->delete();
            DB::commit();
            Log::info('Баннеры удалены: ', $bannerIds);
            // Формируем сообщение об успехе
            $message = "Выбранные бвннеры ({$count} шт.) успешно удалены.";
            // Редирект на индексную страницу с сообщением
            return redirect()->route('admin.banners.index')->with('success', $message);
        } catch (Throwable $e) {
            DB::rollBack();
            Log::error("Ошибка при массовом удалении баннеров: " . $e->getMessage(), ['ids' => $bannerIds]);
            // Редирект назад с сообщением об ошибке
            return back()->withErrors(['general' => 'Произошла ошибка при удалении баннеров.']);
        }
    }

    /**
     * Включение Баннера в левом сайдбаре
     * Использует Route Model Binding и UpdateLeftRequest.
     *
     * @param UpdateLeftRequest $request
     * @param Banner $banner
     * @return RedirectResponse
     */
    public function updateLeft(UpdateLeftRequest $request, Banner $banner): RedirectResponse
    {
        // authorize() в UpdateLeftRequest
        $validated = $request->validated();
        try {
            $banner->left = $validated['left'];
            $banner->save();
            Log::info("Обновлено значение активации в левой колонке для баннера ID {$banner->id}");
            // Формируем сообщение об успехе
            $message = "Выбранные баннеры успешно активированны в левой колонке.";
            return redirect()->route('admin.banners.index')->with('success', $message);
        } catch (Throwable $e) {
            Log::error("Ошибка обновления значение в левой колонке баннера ID {$banner->id}: " . $e->getMessage());
            // Возвращаем редирект НАЗАД с сообщением об ошибке
            return back()->withErrors(['general' => 'Произошла ошибка при обновлении активности в левой колонке.']);
        }
    }

    /**
     * Обновление статуса активности в левой колонке массово
     *
     * @param Request $request
     * @return JsonResponse Json ответ
     */
    public function bulkUpdateLeft(Request $request): JsonResponse
    {
        $data = $request->validate([
            'ids'      => 'required|array',
            'ids.*'    => 'required|integer|exists:banners,id',
            'left' => 'required|boolean',
        ]);

        Banner::whereIn('id', $data['ids'])->update(['left' => $data['left']]);

        return response()->json(['success' => true]);
    }

    /**
     * Включение Баннера в правом сайдбаре
     * Использует Route Model Binding и UpdateRightRequest.
     *
     * @param UpdateRightRequest $request
     * @param Banner $banner
     * @return RedirectResponse
     */
    public function updateRight(UpdateRightRequest $request, Banner $banner): RedirectResponse
    {
        // authorize() в UpdateRightRequest
        $validated = $request->validated();
        try {
            $banner->right = $validated['right'];
            $banner->save();
            Log::info("Обновлено значение активации в правой колонке для баннера ID {$banner->id}");
            // Формируем сообщение об успехе
            $message = "Выбранные баннеры успешно активированны в правой колонке.";
            return redirect()->route('admin.banners.index')->with('success', $message);
        } catch (Throwable $e) {
            Log::error("Ошибка обновления значение в правой колонке баннера ID {$banner->id}: " . $e->getMessage());
            // Возвращаем редирект НАЗАД с сообщением об ошибке
            return back()->withErrors(['general' => 'Произошла ошибка при обновлении активности в правой колонке.']);
        }
    }

    /**
     * Обновление статуса активности в правой колонке массово
     *
     * @param Request $request
     * @return JsonResponse Json ответ
     */
    public function bulkUpdateRight(Request $request): JsonResponse
    {
        $data = $request->validate([
            'ids'      => 'required|array',
            'ids.*'    => 'required|integer|exists:banners,id',
            'right' => 'required|boolean',
        ]);

        Banner::whereIn('id', $data['ids'])->update(['right' => $data['right']]);

        return response()->json(['success' => true]);
    }

    /**
     * Обновление статуса активности баннера.
     * Использует Route Model Binding и UpdateActivityRequest.
     *
     * @param UpdateActivityRequest $request Валидированный запрос с полем 'activity'.
     * @param Banner $banner Модель баннера для обновления.
     * @return RedirectResponse Редирект назад с сообщением.
     */
    public function updateActivity(UpdateActivityRequest $request, Banner $banner): RedirectResponse
    {
        // authorize() в UpdateActivityRequest
        $validated = $request->validated();
        try {
            $banner->activity = $validated['activity'];
            $banner->save();
            $actionText = $banner->activity ? 'активирован' : 'деактивирован';
            Log::info("Обновлено activity баннера ID {$banner->id} на {$banner->activity}");
            // Возвращаем редирект НАЗАД с сообщением об успехе
            return back()->with('success', "Баннер \"{$banner->title}\" {$actionText}.");
        } catch (Throwable $e) {
            Log::error("Ошибка обновления активности баннера ID {$banner->id}: " . $e->getMessage());
            // Возвращаем редирект НАЗАД с сообщением об ошибке
            return back()->withErrors(['general' => 'Произошла ошибка при обновлении активности.']);
        }
    }

    /**
     * Обновление статуса активности массово
     *
     * @param Request $request
     * @return JsonResponse Json ответ
     */
    public function bulkUpdateActivity(Request $request): JsonResponse
    {
        $data = $request->validate([
            'ids'      => 'required|array',
            'ids.*'    => 'required|integer|exists:banners,id',
            'activity' => 'required|boolean',
        ]);

        Banner::whereIn('id', $data['ids'])->update(['activity' => $data['activity']]);

        return response()->json(['success' => true]);
    }

    /**
     * Обновление значения сортировки для одного баннера.
     * Использует Route Model Binding и UpdateSortEntityRequest.
     *
     * @param UpdateSortEntityRequest $request Валидированный запрос с полем 'sort'.
     * @param Banner $banner Модель баннера для обновления.
     * @return RedirectResponse Редирект назад с сообщением..
     */
    public function updateSort(UpdateSortEntityRequest $request, Banner $banner): RedirectResponse
    {
        // authorize() в UpdateSortEntityRequest
        $validated = $request->validated();
        try {
            $banner->sort = $validated['sort'];
            $banner->save();
            Log::info("Обновлено sort баннера ID {$banner->id} на {$banner->sort}");
            return back();

        } catch (Throwable $e) {
            Log::error("Ошибка обновления сортировки баннера ID {$banner->id}: " . $e->getMessage());
            return back()->withErrors(['sort' => 'Не удалось обновить сортировку.']);
        }
    }

    /**
     * Массовое обновление сортировки на основе переданного порядка ID.
     * Принимает массив объектов вида `[{id: 1, sort: 10}, {id: 5, sort: 20}]`.
     *
     * @param Request $request Запрос с массивом 'banners'.
     * @return RedirectResponse Редирект назад с сообщением.
     */
    public function updateSortBulk(Request $request): RedirectResponse
    {
        // TODO: Проверка прав $this->authorize('update-banners');

        // Валидируем входящий массив
        // (Можно вынести в отдельный FormRequest: UpdateSortBulkRequest)
        $validated = $request->validate([
            'banners' => 'required|array',
            'banners.*.id' => ['required', 'integer', 'exists:banners,id'],
            'banners.*.sort' => ['required', 'integer', 'min:1'],
        ]);

        try {
            DB::beginTransaction();

            foreach ($validated['banners'] as $bannerData) {
                // Используем update для массового обновления, если возможно, или where/update
                Banner::where('id', $bannerData['id'])->update(['sort' => $bannerData['sort']]);
            }

            DB::commit();
            Log::info('Массово обновлена сортировка баннеров', ['count' => count($validated['banners'])]);

            return back();

        } catch (Throwable $e) {
            DB::rollBack();
            Log::error("Ошибка массового обновления сортировки баннеров: " . $e->getMessage());

            // Возвращаем редирект назад с ошибкой
            return back()->withErrors(['general' => 'Не удалось обновить порядок баннеров.']);
        }
    }

    /**
     * Приватный метод удаления изображений (для Spatie)
     *
     * @param array $imageIds
     * @return void
     */
    private function deleteImages(array $imageIds): void
    {
        if (empty($imageIds)) return;
        $imagesToDelete = BannerImage::whereIn('id', $imageIds)->get();
        foreach ($imagesToDelete as $image) {
            $image->clearMediaCollection('images');
            $image->delete();
        }
        Log::info('Удалены записи BannerImage и их медиа: ', ['image_ids' => $imageIds]);
    }
}
