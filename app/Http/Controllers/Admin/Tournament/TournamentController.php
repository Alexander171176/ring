<?php

namespace App\Http\Controllers\Admin\Tournament;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Tournament\TournamentRequest;
use App\Http\Requests\Admin\UpdateActivityRequest;
use App\Http\Requests\Admin\UpdateSortEntityRequest;
use App\Http\Resources\Admin\Tournament\TournamentResource;
use App\Http\Resources\Admin\Tournament\TournamentSharedResource;
use App\Models\Admin\Tournament\Tournament;
use App\Models\Admin\Tournament\TournamentImage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
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
            $tournaments = Tournament::with(['images', 'athletes', 'winner'])
                ->withCount('athletes')
                ->get();

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
        // TODO: Проверка прав $this->authorize('create-tournament', Tournament::class);
        return Inertia::render('Admin/Tournaments/Create');
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
        $images = $data['images'] ?? [];
        unset($data['images']);

        try {
            DB::beginTransaction();

            $tournament = Tournament::create($data);
            $this->syncImages($request, $tournament, $images);

            DB::commit();
            return redirect()->route('admin.tournaments.index')
                ->with('success', __('admin/controllers/tournaments.created'));
        } catch (Throwable $e) {
            DB::rollBack();
            Log::error("Ошибка создания турнира: {$e->getMessage()}");
            return back()->withInput()->withErrors(['general' => __('admin/controllers/tournaments.create_error')]);
        }
    }

    public function edit(Tournament $tournament): Response
    {
        $tournament->load(['images' => fn($q) => $q->orderBy('order')]);
        return Inertia::render('Admin/Tournaments/Edit', [
            'tournament' => new TournamentResource($tournament),
        ]);
    }

    public function update(TournamentRequest $request, Tournament $tournament): RedirectResponse
    {
        $data = $request->validated();
        $images = $data['images'] ?? [];
        $deleted = $data['deletedImages'] ?? [];

        unset($data['images'], $data['deletedImages'], $data['_method']);

        try {
            DB::beginTransaction();

            $this->deleteImages($deleted);
            $tournament->update($data);
            $this->syncImages($request, $tournament, $images);

            DB::commit();
            return redirect()->route('admin.tournaments.index')
                ->with('success', __('admin/controllers/tournaments.updated'));
        } catch (Throwable $e) {
            DB::rollBack();
            Log::error("Ошибка обновления турнира ID {$tournament->id}: {$e->getMessage()}");
            return back()->withInput()->withErrors(['general' => __('admin/controllers/tournaments.update_error')]);
        }
    }

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

    private function deleteImages(array $imageIds): void
    {
        $images = TournamentImage::whereIn('id', $imageIds)->get();
        foreach ($images as $image) {
            $image->clearMediaCollection('images');
            $image->delete();
        }
    }
}
