<?php

namespace App\Http\Controllers\Admin\Athlete;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateActivityRequest;
use App\Http\Requests\Admin\UpdateSortEntityRequest;
use App\Models\Admin\Athlete\Athlete;
use App\Models\Admin\Athlete\AthleteImage;
use App\Http\Requests\Admin\Athlete\AthleteRequest;
use App\Http\Resources\Admin\Athlete\AthleteResource;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;
use Throwable;

/**
 * Контроллер для управления Спортсменами в административной панели.
 *
 * Предоставляет CRUD операции, а также дополнительные действия:
 * - Обновление активности и сортировки (одиночное и массовое)
 *
 * @version 1.1 (Улучшен с RMB, транзакциями, Form Requests)
 * @author Александр Косолапов <kosolapov1976@gmail.com>
 * @see \App\Models\Admin\Athlete\Athlete Модель Спортсмена
 * @see \App\Http\Requests\Admin\Athlete\AthleteRequest Запрос для создания/обновления
 */
class AthleteController extends Controller
{
    /**
     * Отображение списка всех Спортсменов.
     * Загружает пагинированный список с сортировкой по настройкам.
     * Передает данные для отображения и настройки пагинации/сортировки.
     * Пагинация и сортировка выполняются на фронтенде.
     *
     * GET /admin/athletes
     * @param Request $request
     * @return Response
     */
    public function index(Request $request): Response
    {
        // TODO: Проверка прав $this->authorize('view-athlete', Athlete::class);

        $adminCountAthletes = config('site_settings.AdminCountAthletes', 15);
        $adminSortAthletes = config('site_settings.AdminSortAthletes', 'idDesc');

        try {
            // Загружаем ВСЕ статьи с секциями и изображениями, счётчики тегов, комментариев, лайков
            $athletes = Athlete::withCount(['images'])
                ->with(['images'])
                ->get();

            $athletesCount = $athletes->count(); // Считаем из загруженной коллекции

        } catch (Throwable $e) {
            Log::error("Ошибка загрузки спортсменов для Index: " . $e->getMessage());
            $athletes = collect(); // Пустая коллекция в случае ошибки
            $athletesCount = 0;
            session()->flash('error', __('admin/controllers/athletes.index_error'));
        }

        return Inertia::render('Admin/Athletes/Index', [
            'athletes' => AthleteResource::collection($athletes),
            'athletesCount' => $athletesCount,
            'adminCountAthletes' => (int)$adminCountAthletes,
            'adminSortAthletes' => $adminSortAthletes,
        ]);
    }

    /**
     * Отображение формы создания нового спортсмена.
     *
     *  GET /admin/athletes/create
     * @return Response
     */
    public function create(): Response
    {
        // TODO: Проверка прав $this->authorize('create-athlete', Athlete::class);
        return Inertia::render('Admin/Athletes/Create');
    }

    /**
     * Сохранение нового спортсмена в базе данных.
     * Использует AthleteRequest для валидации и авторизации.
     * Синхронизирует связанные изображения.
     *
     *  POST /admin/athletes
     * @param AthleteRequest $request
     * @return RedirectResponse Редирект на список статей с сообщением.
     */
    public function store(AthleteRequest $request): RedirectResponse
    {
        $data = $request->validated();
        // Log::debug('🔍 Валидация пройдена', ['validated' => $data]);

        if ($request->hasFile('avatar')) {
            $path = $request->file('avatar')->store('athlete_avatar', 'public');
            $data['avatar'] = $path;
            // Log::debug('📦 Аватар загружен', ['path' => $path]);
        }

        $imagesData = $data['images'] ?? [];
        unset($data['images']);

        DB::beginTransaction();
        try {
            $athlete = Athlete::create($data);

            if (!$athlete || !$athlete->exists) {
                Log::error('❌ Спортсмен не создан!', ['data' => $data]);
                throw new \Exception('Модель спортсмена не была создана');
            }

            // Log::debug('✅ Спортсмен создан', ['id' => $athlete->id, 'nickname' => $athlete->nickname]);

            $imageSyncData = [];
            $imageIndex = 0;

            foreach ($imagesData as $imageData) {
                $fileKey = "images.{$imageIndex}.file";
                // Log::debug('🖼️ Обработка изображения', ['index' => $imageIndex, 'data' => $imageData]);

                if ($request->hasFile($fileKey)) {
                    $image = AthleteImage::create([
                        'order'   => $imageData['order']   ?? 0,
                        'alt'     => $imageData['alt']     ?? '',
                        'caption' => $imageData['caption'] ?? '',
                    ]);

                    try {
                        $file = $request->file($fileKey);

                        if ($file->isValid()) {
                            $image->addMedia($file)->toMediaCollection('images');
                            $imageSyncData[$image->id] = ['order' => $image->order];
                            // Log::debug('✅ Изображение добавлено в медиатеку', ['image_id' => $image->id]);
                        } else {
                            Log::warning("⚠️ Файл изображения невалиден", [
                                'index' => $imageIndex,
                                'fileKey' => $fileKey,
                                'error' => $file->getErrorMessage()
                            ]);
                            $image->delete();
                        }
                    } catch (Throwable $e) {
                        Log::error("❗ Ошибка Spatie при обработке изображения", [
                            'index' => $imageIndex,
                            'message' => $e->getMessage()
                        ]);
                        $image->delete();
                    }
                }

                $imageIndex++;
            }

            $athlete->images()->sync($imageSyncData);
            DB::commit();

            // Log::info('🏁 Спортсмен успешно создан', ['id' => $athlete->id]);
            return redirect()->route('admin.athletes.index')->with('success', __('admin/controllers/athletes.created'));

        } catch (Throwable $e) {
            DB::rollBack();
            Log::error("💥 Ошибка при создании спортсмена", [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return back()->withInput()->withErrors(['general' => __('admin/controllers/athletes.create_error')]);
        }
    }

    /**
     * Отображение формы редактирования существующего спортсмена.
     * Использует Route Model Binding для получения модели.
     *
     *  GET /admin/athletes/{athlete}/edit
     * @param Athlete $athlete Модель спортсмена, найденного по ID из маршрута.
     * @return Response
     */
    public function edit(Athlete $athlete): Response
    {
        // TODO: Проверка прав $this->authorize('update-athlete', $athlete);

        // Загружаем все необходимые связи
        $athlete->load(['images' => fn($q) => $q->orderBy('order', 'asc')]);

        return Inertia::render('Admin/Athletes/Edit', [
            'athlete' => new AthleteResource($athlete),
        ]);
    }

    /**
     * Обновление существующего спортсмена в базе данных.
     * Использует AthleteRequest и Route Model Binding.
     * Синхронизирует связанные изображения, если они переданы.
     *
     *  PUT /admin/athletes/{athlete}
     *  PATCH /admin/athletes/{athlete}
     * @param AthleteRequest $request Валидированный запрос.
     * @param Athlete $athlete Модель спортсмена для обновления.
     * @return RedirectResponse Редирект на список спортсменов с сообщением.
     */
    public function update(AthleteRequest $request, Athlete $athlete): RedirectResponse
    {
        $data = $request->validated();

        // Извлекаем все данные
        $imagesData       = $data['images'] ?? [];
        $deletedImageIds  = $data['deletedImages'] ?? [];

        // Убираем ненужные ключи из $data
        unset(
            $data['images'],
            $data['deletedImages'],
            $data['_method']
        );

        try {
            DB::beginTransaction();

            // 1) Удаляем выбранные пользователем изображения
            if (!empty($deletedImageIds)) {
                // отвязываем от pivot
                $athlete->images()->detach($deletedImageIds);
                // удаляем сами записи и файлы
                $this->deleteImages($deletedImageIds);
            }

            // 2) Обработка аватара (обязательно ДО update)
            if ($request->hasFile('avatar')) {
                // удалить старый файл (если нужно)
                if ($athlete->avatar && Storage::disk('public')->exists($athlete->avatar)) {
                    Storage::disk('public')->delete($athlete->avatar);
                }

                // загрузить новый
                $path = $request->file('avatar')->store('athlete_avatar', 'public');
                $data['avatar'] = $path;
            }

            // 3) Обновляем базовые поля
            $athlete->update($data);

            // 4) Обработка изображений
            $syncData = [];
            foreach ($imagesData as $index => $imageData) {
                $fileKey = "images.{$index}.file";

                // a) Существующее изображение
                if (!empty($imageData['id'])) {
                    $img = AthleteImage::find($imageData['id']);

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
                    $new = AthleteImage::create([
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
            $athlete->images()->sync($syncData);

            DB::commit();

            Log::info('Спортсмен обновлен: ', ['id' => $athlete->id, 'title' => $athlete->nickname]);
            return redirect()->route('admin.athletes.index')->with('success', __('admin/controllers/athletes.updated'));

        } catch (Throwable $e) {
            DB::rollBack();
            Log::error("Ошибка при обновлении спортсмена ID {$athlete->id}: {$e->getMessage()}", [
                'trace' => $e->getTraceAsString(),
            ]);
            return back()->withInput()->withErrors(['general' => __('admin/controllers/athletes.update_error')]);
        }
    }

    /**
     * Удаление указанного спортсмена вместе с изображениями.
     * Использует Route Model Binding. Связи удаляются каскадно.
     *
     *  DELETE /admin/athletes/{athlete}
     * @param Athlete $athlete Модель спортсмена для удаления.
     * @return RedirectResponse Редирект на список спортсменов с сообщением.
     */
    public function destroy(Athlete $athlete): RedirectResponse
    {
        // TODO: Проверка прав $this->authorize('delete-athlete', $athlete);

        try {
            DB::beginTransaction();
            // Удаляем связанные AthleteImage и их медиа, используем приватный метод deleteImages
            $this->deleteImages($athlete->images()->pluck('id')->toArray());
            $athlete->delete(); // Связи с секциями удалятся каскадно
            DB::commit();

            Log::info('Спортсмен удален: ID ' . $athlete->id);
            return redirect()->route('admin.athletes.index')->with('success', __('admin/controllers/athletes.deleted'));

        } catch (Throwable $e) {
            DB::rollBack();
            Log::error("Ошибка при удалении спортсмена ID {$athlete->id}: " . $e->getMessage());
            return back()->withErrors(['general' => __('admin/controllers/athletes.delete_error')]);
        }
    }

    /**
     * Обновление статуса активности баннера.
     * Использует Route Model Binding и UpdateActivityRequest.
     *
     * @param UpdateActivityRequest $request Валидированный запрос с полем 'activity'.
     * @param Athlete $athlete Модель баннера для обновления.
     * @return RedirectResponse Редирект назад с сообщением.
     */
    public function updateActivity(UpdateActivityRequest $request, Athlete $athlete): RedirectResponse
    {
        // authorize() в UpdateActivityRequest
        $validated = $request->validated();

        try {

            DB::beginTransaction();
            $athlete->activity = $validated['activity'];
            $athlete->save();
            DB::commit();

            Log::info("Обновлено activity спортсмена ID {$athlete->id} на {$athlete->activity}");
            $actionText = $athlete->activity ? __('admin/controllers/common.activated')
                : __('admin/controllers/common.deactivated');
            return back()
                ->with('success', __('admin/controllers/athletes.activity', ['title' => $athlete->title, 'action' => $actionText]));

        } catch (Throwable $e) {
            DB::rollBack();
            Log::error("Ошибка обновления активности спортсмена ID {$athlete->id}: " . $e->getMessage());
            return back()->withErrors(['general' => __('admin/controllers/athletes.update_activity_error')]);
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
            'ids.*'    => 'required|integer|exists:athletes,id',
            'activity' => 'required|boolean',
        ]);

        Athlete::whereIn('id', $data['ids'])->update(['activity' => $data['activity']]);

        return response()->json(['success' => true]);
    }

    /**
     * Обновление значения сортировки для одного спортсмена.
     * Использует Route Model Binding и UpdateSortEntityRequest.
     *
     * @param UpdateSortEntityRequest $request Валидированный запрос с полем 'sort'.
     * @param Athlete $athlete Модель спортсмена для обновления.
     * @return RedirectResponse Редирект назад с сообщением..
     */
    public function updateSort(UpdateSortEntityRequest $request, Athlete $athlete): RedirectResponse
    {
        // authorize() в UpdateSortEntityRequest
        $validated = $request->validated();

        try {
            DB::beginTransaction();
            $athlete->sort = $validated['sort'];
            $athlete->save();
            DB::commit();

            Log::info("Обновлено sort спортсмена ID {$athlete->id} на {$athlete->sort}");
            return back();

        } catch (Throwable $e) {
            DB::rollBack();
            Log::error("Ошибка обновления сортировки спортсмена ID {$athlete->id}: " . $e->getMessage());
            return back()->withErrors(['sort' => __('admin/controllers/athletes.update_sort_error')]);
        }
    }

    /**
     * Массовое обновление сортировки на основе переданного порядка ID.
     * Принимает массив объектов вида `[{id: 1, sort: 10}, {id: 5, sort: 20}]`.
     *
     * @param Request $request Запрос с массивом 'athletes'.
     * @return RedirectResponse Редирект назад с сообщением.
     */
    public function updateSortBulk(Request $request): RedirectResponse
    {
        // TODO: Проверка прав $this->authorize('update-athletes');

        // Валидируем входящий массив (Можно вынести в отдельный FormRequest: UpdateSortBulkRequest)
        $validated = $request->validate([
            'athletes' => 'required|array',
            'athletes.*.id' => ['required', 'integer', 'exists:athletes,id'],
            'athletes.*.sort' => ['required', 'integer', 'min:1'],
        ]);

        try {
            DB::beginTransaction();
            foreach ($validated['athletes'] as $athleteData) {
                // Используем update для массового обновления, если возможно, или where/update
                Athlete::where('id', $athleteData['id'])->update(['sort' => $athleteData['sort']]);
            }
            DB::commit();

            Log::info('Массово обновлена сортировка спортсменов', ['count' => count($validated['athletes'])]);
            return back();

        } catch (Throwable $e) {
            DB::rollBack();
            Log::error("Ошибка массового обновления сортировки спортсменов: " . $e->getMessage());
            return back()->withErrors(['general' => __('admin/controllers/athletes.bulk_update_sort_error')]);
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
        $imagesToDelete = AthleteImage::whereIn('id', $imageIds)->get();
        foreach ($imagesToDelete as $image) {
            $image->clearMediaCollection('images');
            $image->delete();
        }
        Log::info('Удалены записи AthleteImage и их медиа: ', ['image_ids' => $imageIds]);
    }

}
