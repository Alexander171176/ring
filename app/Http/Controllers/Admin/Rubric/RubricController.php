<?php

namespace App\Http\Controllers\Admin\Rubric;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Rubric\RubricRequest;
use App\Http\Resources\Admin\Rubric\RubricResource;
use App\Models\Admin\Rubric\Rubric;
use App\Traits\CacheTimeTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class RubricController extends Controller
{
    use CacheTimeTrait;

    /**
     * Показ таблицы всех Рубрик.
     */
    public function index(): \Inertia\Response
    {
        $cacheTime = $this->getCacheTime();

        $rubrics = Cache::store('redis')->remember('rubrics.all', $cacheTime, function () {
            return Rubric::all();
        });

        $rubricsCount = Cache::store('redis')->remember('rubrics.count', $cacheTime, function () {
            return DB::table('rubrics')->count();
        });

        return Inertia::render('Admin/Rubrics/Index', [
            'rubrics' => RubricResource::collection($rubrics),
            'rubricsCount' => $rubricsCount,
        ]);
    }

    /**
     * Показ формы создания рубрики.
     */
    public function create(): \Inertia\Response
    {
        return Inertia::render('Admin/Rubrics/Create');
    }

    /**
     * Создание рубрики.
     */
    public function store(RubricRequest $request): \Illuminate\Http\RedirectResponse
    {
        $data = $request->validated();
        $rubric = Rubric::create($data);

        Log::info('Рубрика успешно создана: ', $rubric->toArray());

        // Очистка кэша после создания рубрики
        $this->clearCache(['rubrics.all', 'rubrics.count']);

        return redirect()->route('rubrics.index')->with('success', 'Рубрика успешно создана');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id): \Inertia\Response
    {
        $cacheTime = $this->getCacheTime();

        $rubric = Cache::store('redis')->remember("rubric.$id", $cacheTime, function () use ($id) {
            return Rubric::findOrFail($id);
        });

        return Inertia::render('Admin/Rubrics/Edit', [
            'rubric' => new RubricResource($rubric),
        ]);
    }

    /**
     * Обновление рубрики.
     */
    public function update(RubricRequest $request, string $id): \Illuminate\Http\RedirectResponse
    {
        $rubric = Rubric::findOrFail($id);
        $data = $request->validated();
        $rubric->update($data);

        Log::info('Рубрика обновлена: ', $rubric->toArray());

        $this->clearCache(['rubrics.all', 'rubrics.count', "rubric.$id"]);

        return redirect()->route('rubrics.index')->with('success', 'Рубрика успешно обновлена');
    }

    /**
     * Удаление рубрики.
     */
    public function destroy(string $id): \Illuminate\Http\RedirectResponse
    {
        $rubric = Rubric::findOrFail($id);
        $rubric->delete();

        Log::info('Рубрика удалена: ', $rubric->toArray());

        $this->clearCache(['rubrics.all', 'rubrics.count', "rubric.$id"]);

        return back();
    }

    /**
     * Удаление выбранных рубрик.
     */
    public function bulkDestroy(Request $request): \Illuminate\Http\JsonResponse
    {
        $validated = $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:rubrics,id',
        ]);

        $rubricIds = $validated['ids'];

        Rubric::whereIn('id', $rubricIds)->each(function ($rubric) {
            $rubric->delete();
        });

        Log::info('Рубрики удалены: ', $rubricIds);

        $this->clearCache(['rubrics.all', 'rubrics.count']);

        return response()->json(['success' => true, 'reload' => true]);
    }

    /**
     * Обновление активности рубрики.
     */
    public function updateActivity(Request $request, $id): \Illuminate\Http\JsonResponse
    {
        $validated = $request->validate([
            'activity' => 'required|boolean',
        ]);

        $rubric = Rubric::findOrFail($id);
        $rubric->activity = $validated['activity'];
        $rubric->save();

        Log::info("Обновлено activity рубрики с ID: $id с данными: ", $validated);

        $this->clearCache(['rubrics.all', "rubric.$id"]);

        return response()->json(['success' => true, 'reload' => true]);
    }

    /**
     * Обновление сортировки рубрик.
     */
    public function updateSort(Request $request, $id): \Illuminate\Http\JsonResponse
    {
        $validated = $request->validate([
            'sort' => 'required|integer',
        ]);

        $rubric = Rubric::findOrFail($id);
        $rubric->sort = $validated['sort'];
        $rubric->save();

        Log::info("Обновлено sort рубрики с ID: $id с данными: ", $validated);

        $this->clearCache(['rubrics.all', "rubric.$id"]);

        return response()->json(['success' => true]);
    }

    /**
     * Клонирование рубрики.
     */
    public function clone(Request $request, $id): \Illuminate\Http\JsonResponse
    {
        DB::beginTransaction(); // Начало транзакции

        try {
            $rubric = Rubric::findOrFail($id);

            // Клонируем рубрику без ID
            $clonedRubric = $rubric->replicate();
            $clonedRubric->title .= '-2'; // Обновляем название клона
            $clonedRubric->url .= '-2';     // Обновляем URL клона
            $clonedRubric->save();

            DB::commit();

            Log::info('Рубрика успешно клонирована: ', $clonedRubric->toArray());

            $this->clearCache(['rubrics.all', 'rubrics.count']);

            return response()->json(['success' => true, 'reload' => true]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Ошибка при клонировании рубрики: ', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Ошибка клонирования рубрики'], 500);
        }
    }
}
