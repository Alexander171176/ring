<?php

namespace App\Http\Controllers\Admin\Parameter;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Setting\SettingRequest;
use App\Http\Resources\Admin\Setting\SettingResource;
use App\Models\Admin\Setting\Setting;
use App\Traits\CacheTimeTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class ParameterController extends Controller
{
    use CacheTimeTrait;

    /**
     * Display a listing of the resource.
     */
    public function index(): \Inertia\Response
    {
        $cacheTime = $this->getCacheTime();

        $settings = Cache::store('redis')->remember('settings.all', $cacheTime, function () {
            return Setting::all();
        });

        $settingsCount = Cache::store('redis')->remember('settings.count', $cacheTime, function () {
            return Setting::count();
        });

        return Inertia::render('Admin/Parameters/Index', [
            'settings' => SettingResource::collection($settings),
            'settingsCount' => $settingsCount,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): \Inertia\Response
    {
        return Inertia::render('Admin/Parameters/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(SettingRequest $request): \Illuminate\Http\RedirectResponse
    {
        $data = $request->validated();
        $setting = Setting::create($data);

        // Log::info('Параметр системы создан: ', $setting->toArray());

        $this->clearCache(['settings.all', 'settings.count']);

        return redirect()->route('parameters.index')->with('success', 'Параметр системы успешно создан');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id): \Inertia\Response
    {
        $cacheTime = $this->getCacheTime();

        $setting = Cache::store('redis')->remember("setting.$id", $cacheTime, function () use ($id) {
            return Setting::findOrFail($id);
        });

        return Inertia::render('Admin/Parameters/Edit', [
            'setting' => new SettingResource($setting),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(SettingRequest $request, string $id): \Illuminate\Http\RedirectResponse
    {
        $setting = Setting::findOrFail($id);
        $data = $request->validated();
        $setting->update($data);

        // Log::info('Параметр системы обновлен: ', $setting->toArray());

        $this->clearCache(['settings.all', 'settings.count', "setting.$id"]);

        return redirect()->route('parameters.index')->with('success', 'Параметр системы успешно обновлен');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id): \Illuminate\Http\RedirectResponse
    {
        $setting = Setting::findOrFail($id);
        $setting->delete();

        // Log::info('Параметр системы удален: ', $setting->toArray());

        $this->clearCache(['settings.all', 'settings.count']);

        return back();
    }

    /**
     * Обновление активности.
     */
    public function updateActivity(Request $request, $id): \Illuminate\Http\JsonResponse
    {
        $validated = $request->validate([
            'activity' => 'boolean',
        ]);

        $setting = Setting::findOrFail($id);
        $setting->activity = $validated['activity'];
        $setting->save();

        // Log::info("Обновлено activity параметра системы с ID: $id с данными: ", $validated);

        $this->clearCache(['settings.all', 'settings.count', "setting.$id"]);

        return response()->json(['success' => true, 'reload' => true]);
    }
}
