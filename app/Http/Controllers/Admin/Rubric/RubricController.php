<?php

namespace App\Http\Controllers\Admin\Rubric;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Rubric\RubricRequest;
use App\Http\Resources\Admin\Rubric\RubricResource;
use App\Models\Admin\Rubric\Rubric;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response;

class RubricController extends Controller
{
    /**
     * Показ таблицы всех Рубрик.
     */
    public function index(): Response
    {
        $rubrics = Rubric::all();
        $rubricsCount = DB::table('rubrics')->count();

        // Получаем значение параметра из конфигурации (оно загружается через AppServiceProvider)
        $adminCountRubrics = config('site_settings.AdminCountRubrics', 10);

        return Inertia::render('Admin/Rubrics/Index', [
            'rubrics' => RubricResource::collection($rubrics),
            'rubricsCount' => $rubricsCount,
            'adminCountRubrics' => (int)$adminCountRubrics,
        ]);
    }

    /**
     * Показ формы создания рубрики.
     */
    public function create(): Response
    {
        return Inertia::render('Admin/Rubrics/Create');
    }

    /**
     * Создание рубрики.
     */
    public function store(RubricRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $rubric = Rubric::create($data);

        // Log::info('Рубрика успешно создана: ', $rubric->toArray());

        return redirect()->route('rubrics.index')->with('success', 'Рубрика успешно создана');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id): Response
    {
        $rubric = Rubric::findOrFail($id);

        return Inertia::render('Admin/Rubrics/Edit', [
            'rubric' => new RubricResource($rubric),
        ]);
    }

    /**
     * Обновление рубрики.
     */
    public function update(RubricRequest $request, string $id): RedirectResponse
    {
        $rubric = Rubric::findOrFail($id);
        $data = $request->validated();
        $rubric->update($data);

        // Log::info('Рубрика обновлена: ', $rubric->toArray());

        return redirect()->route('rubrics.index')->with('success', 'Рубрика успешно обновлена');
    }

    /**
     * Удаление рубрики.
     */
    public function destroy(string $id): RedirectResponse
    {
        $rubric = Rubric::findOrFail($id);
        $rubric->delete();

        // Log::info('Рубрика удалена: ', $rubric->toArray());

        return back();
    }

    /**
     * Удаление выбранных рубрик.
     */
    public function bulkDestroy(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:rubrics,id',
        ]);

        $rubricIds = $validated['ids'];

        Rubric::whereIn('id', $rubricIds)->each(function ($rubric) {
            $rubric->delete();
        });

        // Log::info('Рубрики удалены: ', $rubricIds);

        return response()->json(['success' => true, 'reload' => true]);
    }

    /**
     * Обновление активности рубрики.
     */
    public function updateActivity(Request $request, $id): JsonResponse
    {
        $validated = $request->validate([
            'activity' => 'required|boolean',
        ]);

        $rubric = Rubric::findOrFail($id);
        $rubric->activity = $validated['activity'];
        $rubric->save();

        // Log::info("Обновлено activity рубрики с ID: $id с данными: ", $validated);

        return response()->json(['success' => true, 'reload' => true]);
    }

    /**
     * Обновление сортировки рубрик.
     */
    public function updateSort(Request $request, $id): JsonResponse
    {
        $validated = $request->validate([
            'sort' => 'required|integer',
        ]);

        $rubric = Rubric::findOrFail($id);
        $rubric->sort = $validated['sort'];
        $rubric->save();

        // Log::info("Обновлено sort рубрики с ID: $id с данными: ", $validated);

        return response()->json(['success' => true]);
    }

    /**
     * Клонирование рубрики.
     */
    public function clone(Request $request, $id): JsonResponse
    {
        DB::beginTransaction();

        try {
            $rubric = Rubric::findOrFail($id);

            // Клонируем рубрику без ID
            $clonedRubric = $rubric->replicate();
            $clonedRubric->title .= '-2';
            $clonedRubric->url .= '-2';
            $clonedRubric->save();

            DB::commit();
            // Log::info('Рубрика успешно клонирована: ', $clonedRubric->toArray());
            return response()->json(['success' => true, 'reload' => true]);

        } catch (\Exception $e) {
            DB::rollBack();
            // Log::error('Ошибка при клонировании рубрики: ', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Ошибка клонирования рубрики'], 500);
        }
    }
}
