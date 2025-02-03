<?php

namespace App\Http\Controllers\Admin\Guide;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Guide\GuideRequest;
use App\Http\Resources\Admin\Guide\GuideResource;
use App\Http\Resources\Admin\Tutorial\TutorialResource;
use App\Models\Admin\Guide\Guide;
use App\Models\Admin\Tutorial\Tutorial;
use App\Traits\CacheTimeTrait;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class GuideController extends Controller
{
    use CacheTimeTrait;

    /**
     * Все Гайды
     *
     * @return \Inertia\Response
     */
    public function index(): \Inertia\Response
    {
        $cacheTime = $this->getCacheTime();

        $guides = Cache::store('redis')->remember('guides.all', $cacheTime, function () {
            return Guide::with('tutorials')->get();
        });

        $guidesCount = Cache::store('redis')->remember('guides.count', $cacheTime, function () {
            return Guide::count();
        });

        return Inertia::render('Admin/Guides/Index', [
            'guides' => GuideResource::collection($guides),
            'guidesCount' => $guidesCount,
        ]);
    }

    /**
     * Страница создать Гайд
     *
     * @return \Inertia\Response
     */
    public function create(): \Inertia\Response
    {
        $tutorials = Cache::store('redis')->remember('tutorials.all', $this->getCacheTime(), function () {
            return Tutorial::all();
        });

        return Inertia::render('Admin/Guides/Create', [
            'tutorials' => TutorialResource::collection($tutorials)
        ]);
    }

    /**
     * Создать Гайд
     *
     * @param GuideRequest $request
     * @return RedirectResponse
     */
    public function store(GuideRequest $request): RedirectResponse
    {
        $data = $request->validated();

        if ($request->hasFile('image_url')) {
            $data['image_url'] = $request->file('image_url')->store('guide_images', 'public');
        }

        $tutorialIds = [];
        if ($request->has('tutorials')) {
            $tutorialTitles = array_column($request->input('tutorials'), 'title');
            $tutorialIds = Tutorial::whereIn('title', $tutorialTitles)->pluck('id')->toArray();
        }

        $guide = Guide::create($data);

        if ($tutorialIds) {
            $guide->tutorials()->sync($tutorialIds);
        }

        Log::info('Гайд создан: ', $guide->toArray());

        $this->clearCache(['guides.all', 'guides.count', 'tutorials.all']);

        return redirect()->route('guides.index')->with('success', 'Гайд успешно создан');
    }

    /**
     * Страница редактирования Гайда
     *
     * @param string $id
     * @return \Inertia\Response
     */
    public function edit(string $id): \Inertia\Response
    {
        $cacheTime = $this->getCacheTime();

        // Находим гайд с руководствами
        $guide = Cache::store('redis')->remember("guide.$id", $cacheTime, function () use ($id) {
            return Guide::with('tutorials')->findOrFail($id);
        });

        // Проверка, есть ли изображение, и формирование правильного пути к нему
        if ($guide->image_url) {
            $guide->image_url = Storage::url($guide->image_url);
        }

        // Получаем все руководства
        $tutorials = Cache::store('redis')->remember('tutorials.all', $cacheTime, function () {
            return Tutorial::all();
        });

        // Передаём гайд и руководства на страницу
        return Inertia::render('Admin/Guides/Edit', [
            'guide' => new GuideResource($guide),
            'tutorials' => TutorialResource::collection($tutorials)
        ]);
    }

    /**
     * Обновление Гайда
     *
     * @param GuideRequest $request
     * @param string $id
     * @return RedirectResponse
     */
    public function update(GuideRequest $request, string $id): RedirectResponse
    {
        $guide = Guide::findOrFail($id);

        $data = $request->validated();

        if ($request->hasFile('image_url')) {
            if ($guide->image_url) {
                Storage::disk('public')->delete($guide->image_url);
            }
            $data['image_url'] = $request->file('image_url')->store('guide_images', 'public');
        } else {
            $data['image_url'] = $guide->image_url;
        }

        if ($request->has('tutorials')) {
            $tutorialTitles = array_column($request->input('tutorials'), 'title');
            $tutorialIds = Tutorial::whereIn('title', $tutorialTitles)->pluck('id')->toArray();
            $guide->tutorials()->sync($tutorialIds);
        }

        $guide->update($data);

        Log::info('Гайд обновлен: ', $guide->toArray());

        $this->clearCache(['guides.all', "guide.$id", 'tutorials.all']);

        return redirect()->route('guides.index')->with('success', 'Гайд успешно обновлен');
    }

    /**
     * Удаление Гайда
     *
     * @param string $id
     * @return RedirectResponse
     */
    public function destroy(string $id): RedirectResponse
    {
        $guide = Guide::findOrFail($id);

        if ($guide->image_url) {
            Storage::disk('public')->delete($guide->image_url);
        }
        $guide->delete();

        Log::info('Гайд удален: ', $guide->toArray());

        $this->clearCache(['guides.all', 'guides.count', 'tutorials.all']);

        return back();
    }

    /**
     * Массовые действия над Гайдами
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function bulkDestroy(Request $request): \Illuminate\Http\JsonResponse
    {
        $validated = $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:guides,id',
        ]);

        $guideIds = $validated['ids'];

        Guide::whereIn('id', $guideIds)->each(function ($guide) {
            if ($guide->image_url) {
                Storage::disk('public')->delete($guide->image_url);
            }
            $guide->delete();
        });

        Log::info('Гайды удалены: ', $guideIds);

        $this->clearCache(['guides.all', 'guides.count', 'tutorials.all']);

        return response()->json(['success' => true, 'reload' => true]);
    }

    /**
     * Обновление активности Гайда
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

        $guide = Guide::findOrFail($id);
        $guide->activity = $validated['activity'];
        $guide->save();

        Log::info("Обновлена активность гайда с ID: $id с данными: ", $validated);

        $this->clearCache(['guides.all', "guide.$id"]);

        return response()->json(['success' => true, 'reload' => true]);
    }

    /**
     * Сортировка гайдов
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

        $guide = Guide::findOrFail($id);
        $guide->sort = $validated['sort'];
        $guide->save();

        Log::info("Обновлена сортировка гайда с ID: $id с данными: ", $validated);

        $this->clearCache(['guides.all', "guide.$id"]);

        return response()->json(['success' => true]);
    }

    /**
     * Клонирование Гайда
     *
     * @param Request $request
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function clone(Request $request, $id): \Illuminate\Http\JsonResponse
    {
        $guide = Guide::findOrFail($id);

        $clonedGuide = $guide->replicate();
        $clonedGuide->title = $guide->title . ' 2';
        $clonedGuide->url = $guide->url . '-2';
        $clonedGuide->save();

        $tutorialIds = $guide->tutorials->pluck('id')->toArray();
        if ($tutorialIds) {
            $clonedGuide->tutorials()->sync($tutorialIds);
        }

        Log::info('Гайд клонирован: ', $clonedGuide->toArray());

        $this->clearCache(['guides.all', 'guides.count', 'tutorials.all']);

        return response()->json(['success' => true, 'reload' => true]);
    }

}
