<?php

namespace App\Http\Controllers\Admin\Tournament;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Tournament\TournamentRequest;
use App\Http\Requests\Admin\UpdateActivityRequest;
use App\Http\Requests\Admin\UpdateLeftRequest;
use App\Http\Requests\Admin\UpdateMainRequest;
use App\Http\Requests\Admin\UpdateRightRequest;
use App\Http\Requests\Admin\UpdateSortEntityRequest;
use App\Http\Resources\Admin\Tournament\TournamentResource;
use App\Http\Resources\Admin\Tournament\TournamentSharedResource;
use App\Http\Resources\Admin\Video\VideoSharedResource;
use App\Models\Admin\Athlete\Athlete;
use App\Models\Admin\Tournament\Tournament;
use App\Models\Admin\Tournament\TournamentImage;
use App\Models\Admin\Video\Video;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;
use Throwable;

/**
 * Контроллер для управления Турнирами в административной панели.
 *
 * Предоставляет CRUD операции, а также дополнительные действия:
 * - Обновление активности и сортировки (одиночное и массовое)
 *
 * @version 1.1 (Улучшен с RMB, транзакциями, Form Requests)
 * @author Александр Косолапов <kosolapov1976@gmail.com>
 * @see \App\Models\Admin\Tournament\Tournament Модель Спортсмена
 * @see \App\Http\Requests\Admin\Tournament\TournamentRequest Запрос для создания/обновления
 */
class TournamentController extends Controller
{
    /**
     * Отображение списка всех Турниров.
     * Загружает пагинированный список с сортировкой по настройкам.
     * Передает данные для отображения и настройки пагинации/сортировки.
     * Пагинация и сортировка выполняются на фронтенде.
     *
     * GET /admin/tournaments
     * @return Response
     */
    public function index(): Response
    {
        // TODO: Проверка прав $this->authorize('view-tournament', Tournament::class);

        $adminCountTournaments = config('site_settings.AdminCountTournaments', 15);
        $adminSortTournaments = config('site_settings.AdminSortTournaments', 'idDesc');

        try {
            $tournaments = Tournament::with([
                'fighterRed',
                'fighterBlue',
                'winner',
                'images' => fn($q) => $q->orderBy('order')
            ])->orderBy('sort')->orderByDesc('id')->get();

            $tournamentsCount = $tournaments->count();

        } catch (Throwable $e) {
            Log::error("Ошибка загрузки турниров: {$e->getMessage()}");
            $tournaments = collect();
            $tournamentsCount = 0;
            session()->flash('error', __('admin/controllers/tournaments.index_error'));
        }

        return Inertia::render('Admin/Tournaments/Index', [
            'tournaments' => TournamentResource::collection($tournaments),
            'tournamentsCount' => $tournamentsCount,
            'adminCountTournaments' => (int)$adminCountTournaments,
            'adminSortTournaments' => $adminSortTournaments,
        ]);
    }

    /**
     * Отображение формы создания нового турнира.
     *
     *  GET /admin/tournaments/create
     * @return Response
     */
    public function create(): Response
    {
        $athletes = Athlete::select('id', 'nickname', 'avatar')
            ->orderBy('nickname')
            ->get();

        $videos = Video::select('id', 'title')->orderBy('title')->get();

        return Inertia::render('Admin/Tournaments/Create', [
            'athletes' => $athletes,
            'videos' => VideoSharedResource::collection($videos),
        ]);
    }

    /**
     * Сохранение нового турнира в базе данных.
     * Использует TournamentRequest для валидации и авторизации.
     * Синхронизирует связанные изображения.
     *
     *  POST /admin/tournaments
     * @param TournamentRequest $request
     * @return RedirectResponse Редирект на список турниров с сообщением.
     */
    public function store(TournamentRequest $request): RedirectResponse
    {
        $data = $request->validated();
        // Log::debug('🔍 Валидация пройдена', ['validated' => $data]);

        $imagesData = $data['images'] ?? [];
        $videoIds   = collect($data['videos'] ?? [])->pluck('id')->toArray();
        unset($data['images'], $data['videos']);

        DB::beginTransaction();
        try {
            $tournament = Tournament::create($data);

            if (!$tournament || !$tournament->exists) {
                Log::error('❌ Турнир не создан!', ['data' => $data]);
                throw new \Exception('Модель турнира не была создана');
            }

            // Log::debug('✅ Спортсмен создан', ['id' => $tournament->id, 'nickname' => $tournament->nickname]);

            $imageSyncData = [];
            $imageIndex = 0;

            foreach ($imagesData as $imageData) {
                $fileKey = "images.{$imageIndex}.file";
                // Log::debug('🖼️ Обработка изображения', ['index' => $imageIndex, 'data' => $imageData]);

                if ($request->hasFile($fileKey)) {
                    $image = TournamentImage::create([
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

            $tournament->videos()->sync($videoIds);
            $tournament->images()->sync($imageSyncData);
            DB::commit();

            // Log::info('🏁 Турнир успешно создан', ['id' => $tournament->id]);
            return redirect()->route('admin.tournaments.index')->with('success', __('admin/controllers/tournaments.created'));

        } catch (Throwable $e) {
            DB::rollBack();
            Log::error("💥 Ошибка при создании турнира", [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return back()->withInput()->withErrors(['general' => __('admin/controllers/tournaments.create_error')]);
        }
    }

    /**
     * Отображение формы редактирования существующего турнира.
     * Использует Route Model Binding для получения модели.
     *
     *  GET /admin/tournaments/{tournament}/edit
     * @param Tournament $tournament Модель турнира, найденного по ID из маршрута.
     * @return Response
     */
    public function edit(Tournament $tournament): Response
    {
        // TODO: Проверка прав $this->authorize('update-tournaments', $tournament);

        $tournament->load([
            'images' => fn($q) => $q->orderBy('order'),
            'fighterRed', 'fighterBlue', 'winner', 'videos',
        ]);

        $athletes = Athlete::select('id', 'nickname', 'avatar')->orderBy('nickname')->get();
        $videos = Video::select('id', 'title')->orderBy('title')->get();

        return Inertia::render('Admin/Tournaments/Edit', [
            'tournament' => new TournamentResource($tournament),
            'athletes' => $athletes,
            'videos' => $videos,
        ]);
    }

    /**
     * Обновление существующего турнира в базе данных.
     * Использует TournamentRequest и Route Model Binding.
     * Синхронизирует связанные изображения, если они переданы.
     *
     *  PUT /admin/tournaments/{tournament}
     *  PATCH /admin/tournaments/{tournament}
     * @param TournamentRequest $request Валидированный запрос.
     * @param Tournament $tournament Модель турнира для обновления.
     * @return RedirectResponse Редирект на список турниров с сообщением.
     */
    public function update(TournamentRequest $request, Tournament $tournament): RedirectResponse
    {
        $data = $request->validated();

        // Извлекаем все данные
        $imagesData       = $data['images'] ?? [];
        $deletedImageIds  = $data['deletedImages'] ?? [];
        $videoIds       = collect($data['videos'] ?? [])->pluck('id')->toArray();

        // Убираем ненужные ключи из $data
        unset(
            $data['videos'],
            $data['images'],
            $data['deletedImages'],
            $data['_method']
        );

        try {
            DB::beginTransaction();

            // 1) Удаляем выбранные пользователем изображения
            if (!empty($deletedImageIds)) {
                // отвязываем от pivot
                $tournament->images()->detach($deletedImageIds);
                // удаляем сами записи и файлы
                $this->deleteImages($deletedImageIds);
            }

            // 2) Обновляем базовые поля
            $tournament->update($data);

            // 3) Обработка изображений
            $syncData = [];
            foreach ($imagesData as $index => $imageData) {
                $fileKey = "images.{$index}.file";

                // a) Существующее изображение
                if (!empty($imageData['id'])) {
                    $img = TournamentImage::find($imageData['id']);

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
                    $new = TournamentImage::create([
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
            $tournament->images()->sync($syncData);
            $tournament->videos()->sync($videoIds);

            DB::commit();

            Log::info('Турнир обновлен: ', ['id' => $tournament->id, 'title' => $tournament->nickname]);
            return redirect()->route('admin.tournaments.index')->with('success', __('admin/controllers/tournaments.updated'));

        } catch (Throwable $e) {
            DB::rollBack();
            Log::error("Ошибка при обновлении турнира ID {$tournament->id}: {$e->getMessage()}", [
                'trace' => $e->getTraceAsString(),
            ]);
            return back()->withInput()->withErrors(['general' => __('admin/controllers/tournaments.update_error')]);
        }
    }

    /**
     * Удаление указанного турнира вместе с изображениями.
     * Использует Route Model Binding. Связи удаляются каскадно.
     *
     *  DELETE /admin/tournaments/{tournament}
     * @param Tournament $tournament Модель турнира для удаления.
     * @return RedirectResponse Редирект на список турниров с сообщением.
     */
    public function destroy(Tournament $tournament): RedirectResponse
    {
        try {
            DB::beginTransaction();
            $this->deleteImages($tournament->images()->pluck('id')->toArray());
            $tournament->delete();
            DB::commit();

            return redirect()->route('admin.tournaments.index')
                ->with('success', __('admin/controllers/tournaments.deleted'));
        } catch (Throwable $e) {
            DB::rollBack();
            Log::error("Ошибка удаления турнира: {$e->getMessage()}");
            return back()->withErrors(['general' => __('admin/controllers/tournaments.delete_error')]);
        }
    }

    /**
     * Включение Турнира в левом сайдбаре
     * Использует Route Model Binding и UpdateLeftRequest.
     *
     * @param UpdateLeftRequest $request
     * @param Tournament $tournament
     * @return RedirectResponse
     */
    public function updateLeft(UpdateLeftRequest $request, Tournament $tournament): RedirectResponse
    {
        // authorize() в UpdateLeftRequest
        $validated = $request->validated();

        try {
            DB::beginTransaction();
            $tournament->left = $validated['left'];
            $tournament->save();
            DB::commit();

            Log::info("Обновлено значение активации в левой колонке для турнира ID {$tournament->id}");
            return back()->with('success', __('admin/controllers/tournaments.left_updated'));

        } catch (Throwable $e) {
            DB::rollBack();
            Log::error("Ошибка обновления значение в левой колонке турнира ID {$tournament->id}: " . $e->getMessage());
            return back()->withErrors(['general' => __('admin/controllers/tournaments.left_update_error')]);
        }
    }

    /**
     * Включение Главными
     * Использует Route Model Binding и UpdateMainRequest.
     *
     * @param UpdateMainRequest $request
     * @param Tournament $tournament
     * @return RedirectResponse
     */
    public function updateMain(UpdateMainRequest $request, Tournament $tournament): RedirectResponse
    {
        // authorize() в UpdateMainRequest
        $validated = $request->validated();

        try {
            DB::beginTransaction();
            $tournament->main = $validated['main'];
            $tournament->save();
            DB::commit();

            Log::info("Обновлено значение активации в главном для турнира ID {$tournament->id}");
            return back()->with('success', __('admin/controllers/tournaments.main_updated'));

        } catch (Throwable $e) {
            DB::rollBack();
            Log::error("Ошибка обновления значение в главном турнира ID {$tournament->id}: " . $e->getMessage());
            return back()->withErrors(['general' => __('admin/controllers/tournaments.main_update_error')]);
        }
    }

    /**
     * Включение Турнира в правом сайдбаре
     * Использует Route Model Binding и UpdateRightRequest.
     *
     * @param UpdateRightRequest $request
     * @param Tournament $tournament
     * @return RedirectResponse
     */
    public function updateRight(UpdateRightRequest $request, Tournament $tournament): RedirectResponse
    {
        // authorize() в UpdateRightRequest
        $validated = $request->validated();

        try {
            DB::beginTransaction();
            $tournament->right = $validated['right'];
            $tournament->save();
            DB::commit();

            Log::info("Обновлено значение активации в правой колонке для турнира ID {$tournament->id}");
            return back()->with('success', __('admin/controllers/tournaments.right_updated'));

        } catch (Throwable $e) {
            DB::rollBack();
            Log::error("Ошибка обновления значение в правой колонке турнира ID {$tournament->id}: " . $e->getMessage());
            return back()->withErrors(['general' => __('admin/controllers/tournaments.right_update_error')]);
        }
    }


    /**
     * Обновление статуса активности турнира.
     * Использует Route Model Binding и UpdateActivityRequest.
     *
     * @param UpdateActivityRequest $request Валидированный запрос с полем 'activity'.
     * @param Tournament $tournament Модель турнира для обновления.
     * @return RedirectResponse Редирект назад с сообщением.
     */
    public function updateActivity(UpdateActivityRequest $request, Tournament $tournament): RedirectResponse
    {
        $validated = $request->validated();

        try {
            $tournament->activity = $validated['activity'];
            $tournament->save();

            $action = $tournament->activity ? __('admin/controllers/common.activated')
                : __('admin/controllers/common.deactivated');

            return back()->with('success', __('admin/controllers/tournaments.activity', [
                'title' => $tournament->name,
                'action' => $action,
            ]));
        } catch (Throwable $e) {
            Log::error("Ошибка обновления активности турнира ID {$tournament->id}: {$e->getMessage()}");
            return back()->withErrors(['general' => __('admin/controllers/tournaments.update_activity_error')]);
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
        // TODO: Проверка прав доступа $this->authorize('update-tournaments', Tournament::class);
        $data = $request->validate([
            'ids'      => 'required|array',
            'ids.*'    => 'required|integer|exists:tournaments,id',
            'activity' => 'required|boolean',
        ]);

        Tournament::whereIn('id', $data['ids'])->update(['activity' => $data['activity']]);

        return response()->json(['success' => true]);
    }

    /**
     * Обновление значения сортировки для одного турнира.
     * Использует Route Model Binding и UpdateSortEntityRequest.
     *
     * @param UpdateSortEntityRequest $request Валидированный запрос с полем 'sort'.
     * @param Tournament $tournament Модель турнира для обновления.
     * @return RedirectResponse Редирект назад с сообщением..
     */
    public function updateSort(UpdateSortEntityRequest $request, Tournament $tournament): RedirectResponse
    {
        $validated = $request->validated();

        try {
            $tournament->sort = $validated['sort'];
            $tournament->save();
            return back();
        } catch (Throwable $e) {
            Log::error("Ошибка сортировки турнира ID {$tournament->id}: {$e->getMessage()}");
            return back()->withErrors(['sort' => __('admin/controllers/tournaments.update_sort_error')]);
        }
    }

    /**
     * Массовое обновление сортировки на основе переданного порядка ID.
     * Принимает массив объектов вида `[{id: 1, sort: 10}, {id: 5, sort: 20}]`.
     *
     * @param Request $request Запрос с массивом 'tournaments'.
     * @return RedirectResponse Редирект назад с сообщением.
     */
    public function updateSortBulk(Request $request): RedirectResponse
    {
        // TODO: Проверка прав $this->authorize('update-tournaments');

        // Валидируем входящий массив (Можно вынести в отдельный FormRequest: UpdateSortBulkRequest)
        $validated = $request->validate([
            'tournaments' => 'required|array',
            'tournaments.*.id' => ['required', 'integer', 'exists:tournaments,id'],
            'tournaments.*.sort' => ['required', 'integer', 'min:1'],
        ]);

        try {
            DB::beginTransaction();
            foreach ($validated['tournaments'] as $tournamentData) {
                // Используем update для массового обновления, если возможно, или where/update
                Tournament::where('id', $tournamentData['id'])->update(['sort' => $tournamentData['sort']]);
            }
            DB::commit();

            Log::info('Массово обновлена сортировка турниров', ['count' => count($validated['tournaments'])]);
            return back();

        } catch (Throwable $e) {
            DB::rollBack();
            Log::error("Ошибка массового обновления сортировки турниров: " . $e->getMessage());
            return back()->withErrors(['general' => __('admin/controllers/tournaments.bulk_update_sort_error')]);
        }
    }

    private function syncImages(Request $request, Tournament $tournament, array $images): void
    {
        $syncData = [];

        foreach ($images as $index => $imageData) {
            $fileKey = "images.{$index}.file";

            if (!empty($imageData['id'])) {
                $image = TournamentImage::find($imageData['id']);

                if ($image && $request->hasFile($fileKey)) {
                    $image->clearMediaCollection('images');
                    $image->addMedia($request->file($fileKey))
                        ->toMediaCollection('images');
                }

                $image?->update([
                    'order' => $imageData['order'] ?? $image->order,
                    'alt' => $imageData['alt'] ?? $image->alt,
                    'caption' => $imageData['caption'] ?? $image->caption,
                ]);

                $syncData[$image->id] = ['order' => $image->order];

            } elseif ($request->hasFile($fileKey)) {
                $image = TournamentImage::create([
                    'order' => $imageData['order'] ?? 0,
                    'alt' => $imageData['alt'] ?? '',
                    'caption' => $imageData['caption'] ?? '',
                ]);
                $image->addMedia($request->file($fileKey))
                    ->toMediaCollection('images');

                $syncData[$image->id] = ['order' => $image->order];
            }
        }

        $tournament->images()->sync($syncData);
    }

    /**
     * Приватный метод удаления изображений (для Spatie)
     *
     * @param array $imageIds
     * @return void
     */
    private function deleteImages(array $imageIds): void
    {
        $images = TournamentImage::whereIn('id', $imageIds)->get();
        foreach ($images as $image) {
            $image->clearMediaCollection('images');
            $image->delete();
        }
    }
}
