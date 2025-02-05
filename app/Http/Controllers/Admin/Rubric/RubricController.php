<?php

namespace App\Http\Controllers\Admin\Rubric;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Rubric\RubricRequest;
use App\Http\Resources\Admin\Rubric\RubricResource;
use App\Models\Admin\Rubric\Rubric;
use App\Traits\CacheTimeTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;
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
            return Rubric::with('translations')->get();
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
     * Show the form for creating a new resource.
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

        if ($request->hasFile('image_url')) {
            $data['image_url'] = $request->file('image_url')->store('rubric_images', 'public');
        }

        $rubric = Rubric::create($data);

        Log::info('Рубрика создана: ', $rubric->toArray());

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
            $rubric = Rubric::findOrFail($id);

            if ($rubric->image_url) {
                $rubric->image_url = Storage::url($rubric->image_url);
            }

            return $rubric;
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

        if ($request->hasFile('image_url')) {
            if ($rubric->image_url) {
                Storage::disk('public')->delete($rubric->image_url);
            }
            $data['image_url'] = $request->file('image_url')->store('rubric_images', 'public');
        } else {
            $data['image_url'] = $rubric->image_url;
        }

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

        if ($rubric->image_url) {
            Storage::disk('public')->delete($rubric->image_url);
        }
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
            if ($rubric->image_url) {
                Storage::disk('public')->delete($rubric->image_url);
            }
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
        DB::beginTransaction();

        try {
            $rubric = Rubric::with('translations')->findOrFail($id);

            // Клонируем рубрику без ID
            $clonedRubric = $rubric->replicate();
            $clonedRubric->save();

            // Клонируем переводы, добавляя -2 к title и url
            foreach ($rubric->translations as $translation) {
                $clonedTranslation = $translation->replicate();
                $clonedTranslation->rubric_id = $clonedRubric->id;
                $clonedTranslation->title = $translation->title . '-2'; // Добавляем -2 в конец title
                $clonedTranslation->url = $translation->url . '-2'; // Добавляем -2 в конец url
                $clonedTranslation->save();
            }

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
