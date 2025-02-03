<?php

namespace App\Http\Controllers\Admin\About;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\About\SectionRequest;
use App\Http\Resources\Admin\About\SectionResource;
use App\Models\Admin\About\Section;
use App\Traits\CacheTimeTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class SectionController extends Controller
{
    use CacheTimeTrait;

    /**
     * Показ таблицы всех Рубрик.
     */
    public function index(): \Inertia\Response
    {
        $cacheTime = $this->getCacheTime();

        $sections = Cache::store('redis')->remember('sections.all', $cacheTime, function () {
            return Section::all();
        });

        $sectionsCount = Cache::store('redis')->remember('sections.count', $cacheTime, function () {
            return DB::table('sections')->count();
        });

        return Inertia::render('Admin/Abouts/Index', [
            'sections' => SectionResource::collection($sections),
            'sectionsCount' => $sectionsCount,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): \Inertia\Response
    {
        return Inertia::render('Admin/Abouts/Create');
    }

    /**
     * Создание секции.
     */
    public function store(SectionRequest $request): \Illuminate\Http\RedirectResponse
    {
        $data = $request->validated();

        // Проверяем, загружено ли изображение
        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('abouts_images', 'public');
        } else {
            // Устанавливаем значение по умолчанию для 'image', если изображение не загружено
            $data['image'] = 'default_image_path.jpg'; // Замените на ваш путь по умолчанию
        }

        $section = Section::create($data);

        Log::info('Секция создана: ', $section->toArray());

        $this->clearCache(['sections.all', 'sections.count']);

        return redirect()->route('abouts.index')->with('success', 'Секция успешно создана');
    }


    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id): \Inertia\Response
    {
        $cacheTime = $this->getCacheTime();

        $section = Cache::store('redis')->remember("section.$id", $cacheTime, function () use ($id) {
            $section = Section::findOrFail($id);

            // Проверка, есть ли изображение, и формирование правильного пути к нему
            if ($section->image_url) {
                $section->image_url = Storage::url($section->image_url);
            }

            return $section;
        });

        return Inertia::render('Admin/Abouts/Edit', [
            'section' => new SectionResource($section),
        ]);
    }

    /**
     * Обновление секции.
     */
    public function update(SectionRequest $request, string $id): \Illuminate\Http\RedirectResponse
    {
        $section = Section::findOrFail($id);

        $data = $request->validated();

        if ($request->hasFile('image')) {
            if ($section->image) {
                Storage::disk('public')->delete($section->image);
            }
            $data['image'] = $request->file('image')->store('abouts_images', 'public');
        } else {
            $data['image'] = $section->image;
        }

        $section->update($data);

        Log::info('Секция обновлена: ', $section->toArray());

        $this->clearCache(['sections.all', 'sections.count', "section.$id"]);

        return redirect()->route('abouts.index')->with('success', 'Секция успешно обновлена');
    }

    /**
     * Удаление секции.
     */
    public function destroy(string $id): \Illuminate\Http\RedirectResponse
    {
        $section = Section::findOrFail($id);

        if ($section->image) {
            Storage::disk('public')->delete($section->image);
        }
        $section->delete();

        Log::info('Секция удалена: ', $section->toArray());

        $this->clearCache(['sections.all', 'sections.count', "section.$id"]);

        return back();
    }

    /**
     * Удаление выбранных рубрик.
     */
    public function bulkDestroy(Request $request): \Illuminate\Http\JsonResponse
    {
        $validated = $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:sections,id',
        ]);

        $sectionIds = $validated['ids'];

        Section::whereIn('id', $sectionIds)->each(function ($section) {
            if ($section->image) {
                Storage::disk('public')->delete($section->image);
            }
            $section->delete();
        });

        Log::info('Секции удалены: ', $sectionIds);

        $this->clearCache(['sections.all', 'sections.count']);

        return response()->json(['success' => true, 'reload' => true]);
    }

    /**
     * Обновление активности секции.
     */
    public function updateActivity(Request $request, $id): \Illuminate\Http\JsonResponse
    {
        $validated = $request->validate([
            'activity' => 'required|boolean',
        ]);

        $section = Section::findOrFail($id);
        $section->activity = $validated['activity'];
        $section->save();

        Log::info("Обновлено activity секции с ID: $id с данными: ", $validated);

        $this->clearCache(['sections.all', "section.$id"]);

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

        $section = Section::findOrFail($id);
        $section->sort = $validated['sort'];
        $section->save();

        Log::info("Обновлено sort секции с ID: $id с данными: ", $validated);

        $this->clearCache(['sections.all', "section.$id"]);

        return response()->json(['success' => true]);
    }

    /**
     * Клонирование секции.
     */
    public function clone(Request $request, $id): \Illuminate\Http\JsonResponse
    {
        $section = Section::findOrFail($id);

        $clonedSection = $section->replicate();
        $clonedSection->title = $section->title . ' 2';

        if ($section->image) {
            $clonedSection->image = $section->image;
        }

        $clonedSection->save();

        Log::info('Секция клонирована: ', $clonedSection->toArray());

        $this->clearCache(['sections.all', 'sections.count']);

        return response()->json(['success' => true, 'reload' => true]);
    }

}
