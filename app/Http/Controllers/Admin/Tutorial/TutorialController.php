<?php

namespace App\Http\Controllers\Admin\Tutorial;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Tutorial\TutorialRequest;
use App\Http\Resources\Admin\Tutorial\TutorialResource;
use App\Models\Admin\Tutorial\Tutorial;
use App\Traits\CacheTimeTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class TutorialController extends Controller
{
    use CacheTimeTrait;

    /**
     * Показ таблицы всех Руководств
     *
     * @return \Inertia\Response
     */
    public function index(): \Inertia\Response
    {
        $cacheTime = $this->getCacheTime();

        $tutorials = Cache::store('redis')->remember('tutorials.all', $cacheTime, function () {
            return Tutorial::all();
        });

        $tutorialsCount = Cache::store('redis')->remember('tutorials.count', $cacheTime, function () {
            return DB::table('tutorials')->count();
        });

        return Inertia::render('Admin/Tutorials/Index', [
            'tutorials' => TutorialResource::collection($tutorials),
            'tutorialsCount' => $tutorialsCount,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): \Inertia\Response
    {
        return Inertia::render('Admin/Tutorials/Create');
    }

    /**
     * Создание руководства
     *
     * @param TutorialRequest $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(TutorialRequest $request): \Illuminate\Http\RedirectResponse
    {
        $data = $request->validated();

        if ($request->hasFile('image_url')) {
            $data['image_url'] = $request->file('image_url')->store('tutorial_images', 'public');
        }

        $tutorial = Tutorial::create($data);

        Log::info('Руководство создано: ', $tutorial->toArray());

        $this->clearCache(['tutorials']);

        return redirect()->route('tutorials.index')->with('success', 'Руководство успешно создано');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id): \Inertia\Response
    {
        $cacheTime = $this->getCacheTime();

        $tutorial = Cache::store('redis')->remember("tutorial.$id", $cacheTime, function () use ($id) {
            $tutorial = Tutorial::findOrFail($id);

            // Проверка, есть ли изображение, и формирование правильного пути к нему
            if ($tutorial->image_url) {
                $tutorial->image_url = Storage::url($tutorial->image_url);
            }

            return $tutorial;
        });

        return Inertia::render('Admin/Tutorials/Edit', [
            'tutorial' => new TutorialResource($tutorial),
        ]);
    }

    /**
     * Обновление руководства
     *
     * @param TutorialRequest $request
     * @param string $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(TutorialRequest $request, string $id): \Illuminate\Http\RedirectResponse
    {
        $tutorial = Tutorial::findOrFail($id);

        $data = $request->validated();

        if ($request->hasFile('image_url')) {
            if ($tutorial->image_url) {
                Storage::disk('public')->delete($tutorial->image_url);
            }
            $data['image_url'] = $request->file('image_url')->store('tutorial_images', 'public');
        } else {
            $data['image_url'] = $tutorial->image_url;
        }

        $tutorial->update($data);

        Log::info('Руководство обновлено: ', $tutorial->toArray());

        $this->clearCache(['tutorials']);

        return redirect()->route('tutorials.index')->with('success', 'Руководство успешно обновлено');
    }

    /**
     * Удаление руководства
     *
     * @param string $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(string $id): \Illuminate\Http\RedirectResponse
    {
        $tutorial = Tutorial::findOrFail($id);

        if ($tutorial->image_url) {
            Storage::disk('public')->delete($tutorial->image_url);
        }
        $tutorial->delete();

        Log::info('Руководство удалено: ', $tutorial->toArray());

        $this->clearCache(['tutorials']);

        return back();
    }

    /**
     * Удаление выбранных руководств
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function bulkDestroy(Request $request): \Illuminate\Http\JsonResponse
    {
        $validated = $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:tutorials,id',
        ]);

        $tutorialIds = $validated['ids'];

        Tutorial::whereIn('id', $tutorialIds)->each(function ($tutorial) {
            if ($tutorial->image_url) {
                Storage::disk('public')->delete($tutorial->image_url);
            }
            $tutorial->delete();
        });

        Log::info('Руководства удалены: ', $tutorialIds);

        $this->clearCache(['tutorials']);

        return response()->json(['success' => true, 'reload' => true]);
    }

    /**
     * Обновление активности руководства
     *
     * @param Request $request
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateActivity(Request $request, $id): \Illuminate\Http\JsonResponse
    {
        $validated = $request->validate([
            'activity' => 'required|boolean',
        ]);

        $tutorial = Tutorial::findOrFail($id);
        $tutorial->activity = $validated['activity'];
        $tutorial->save();

        Log::info("Обновлено activity руководства с ID: $id с данными: ", $validated);

        $this->clearCache(['tutorials']);

        return response()->json(['success' => true, 'reload' => true]);
    }

    /**
     * Обновление сортировки руководств
     *
     * @param Request $request
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateSort(Request $request, $id): \Illuminate\Http\JsonResponse
    {
        $validated = $request->validate([
            'sort' => 'required|integer',
        ]);

        $tutorial = Tutorial::findOrFail($id);
        $tutorial->sort = $validated['sort'];
        $tutorial->save();

        Log::info("Обновлено sort руководства с ID: $id с данными: ", $validated);

        $this->clearCache(['tutorials']);

        return response()->json(['success' => true]);
    }

    /**
     * Клонирование руководства
     *
     * @param Request $request
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function clone(Request $request, $id): \Illuminate\Http\JsonResponse
    {
        $tutorial = Tutorial::findOrFail($id);

        $clonedTutorial = $tutorial->replicate();
        $clonedTutorial->title = $tutorial->title . ' 2';
        $clonedTutorial->url = $tutorial->url . '-2';

        if ($tutorial->image_url) {
            $clonedTutorial->image_url = $tutorial->image_url;
        }

        $clonedTutorial->save();

        Log::info('Руководство клонировано: ', $clonedTutorial->toArray());

        $this->clearCache(['tutorials']);

        return response()->json(['success' => true, 'reload' => true]);
    }

}
