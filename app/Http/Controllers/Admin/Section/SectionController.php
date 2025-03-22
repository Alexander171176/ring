<?php

namespace App\Http\Controllers\Admin\Section;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Section\SectionRequest;
use App\Http\Resources\Admin\Rubric\RubricResource;
use App\Http\Resources\Admin\Section\SectionResource;
use App\Models\Admin\Rubric\Rubric;
use App\Models\Admin\Section\Section;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response;

class SectionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): Response
    {
        $sections = Section::with(['rubrics'])->get();
        $sectionsCount = Section::count();

        return Inertia::render('Admin/Sections/Index', [
            'sections' => SectionResource::collection($sections),
            'sectionsCount' => $sectionsCount,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): Response
    {
        // Загрузка рубрик напрямую
        $rubrics = Rubric::all();

        return Inertia::render('Admin/Sections/Create', [
            'rubrics' => RubricResource::collection($rubrics),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(SectionRequest $request): RedirectResponse
    {
        $data = $request->validated();

        // Найти рубрики по title
        $rubricIds = [];
        if ($request->has('rubrics')) {
            $rubricTitles = array_column($request->input('rubrics'), 'title');
            $rubricIds = Rubric::whereIn('title', $rubricTitles)->pluck('id')->toArray();
        }

        // Создаем секцию
        $section = Section::create($data);

        // Привязываем рубрики
        if ($rubricIds) {
            $section->rubrics()->sync($rubricIds);
        }

        return redirect()->route('sections.index')->with('success', 'Секция успешно создана.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id): Response
    {
        $section = Section::with(['rubrics'])->findOrFail($id);
        $rubrics = Rubric::all();

        return Inertia::render('Admin/Sections/Edit', [
            'section' => new SectionResource($section),
            'rubrics' => RubricResource::collection($rubrics),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(SectionRequest $request, string $id): RedirectResponse
    {
        $section = Section::findOrFail($id);
        $data = $request->validated();

        // Обновляем данные секции
        $section->update($data);

        // Обновляем связи рубрик
        $rubricIds = $request->has('rubrics')
            ? Rubric::whereIn('title', array_column($request->input('rubrics'), 'title'))->pluck('id')->toArray()
            : [];
        $section->rubrics()->sync($rubricIds);

        return redirect()->route('sections.index')->with('success', 'Секция успешно обновлена.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id): RedirectResponse
    {
        $section = Section::findOrFail($id);
        $section->delete();

        Log::info('Секция удалена: ', $section->toArray());

        return back()->with('success', 'Секция удалена.');
    }

    /**
     * Массовые действия над Секциями.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function bulkDestroy(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'ids'   => 'required|array',
            'ids.*' => 'exists:sections,id',
        ]);

        $sectionIds = $validated['ids'];

        Section::whereIn('id', $sectionIds)->each(function ($section) {
            $section->delete();
        });

        Log::info('Секции удалены: ', $sectionIds);

        return response()->json(['success' => true, 'reload' => true]);
    }

    /**
     * Обновление активности Секции.
     *
     * @param Request $request
     * @param mixed $id
     * @return JsonResponse
     */
    public function updateActivity(Request $request, $id): JsonResponse
    {
        $validated = $request->validate([
            'activity' => 'required|boolean',
        ]);

        $section = Section::findOrFail($id);
        $section->activity = $validated['activity'];
        $section->save();

        Log::info("Обновлена активность секции с ID: $id с данными: ", $validated);

        return response()->json(['success' => true, 'reload' => true]);
    }

    /**
     * Сортировка Секций.
     *
     * @param Request $request
     * @param mixed $id
     * @return JsonResponse
     */
    public function updateSort(Request $request, $id): JsonResponse
    {
        $validated = $request->validate([
            'sort' => 'required|integer',
        ]);

        $section = Section::findOrFail($id);
        $section->sort = $validated['sort'];
        $section->save();

        Log::info("Обновлена сортировка секции с ID: $id с данными: ", $validated);

        return response()->json(['success' => true]);
    }

    /**
     * Клонирование Секции.
     *
     * @param Request $request
     * @param mixed $id
     * @return JsonResponse
     */
    public function clone(Request $request, $id): JsonResponse
    {
        $section = Section::findOrFail($id);

        // Клонирование секции
        $clonedSection = $section->replicate();
        $clonedSection->title = $section->title . ' 2';
        $clonedSection->save();

        // Копируем рубрики
        $rubricIds = $section->rubrics->pluck('id')->toArray();
        if ($rubricIds) {
            $clonedSection->rubrics()->sync($rubricIds);
        }

        Log::info('Секция клонирована: ', $clonedSection->toArray());

        return response()->json(['success' => true, 'reload' => true]);
    }
}
