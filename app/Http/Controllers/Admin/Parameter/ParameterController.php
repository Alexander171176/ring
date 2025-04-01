<?php

namespace App\Http\Controllers\Admin\Parameter;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Setting\SettingRequest;
use App\Http\Resources\Admin\Setting\SettingResource;
use App\Models\Admin\Setting\Setting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response;

class ParameterController extends Controller
{

    /**
     * Display a listing of the resource.
     */
    public function index(): Response
    {

        $settings = Setting::all();
        $settingsCount = Setting::count();

        // Получаем значение параметра из конфигурации (оно загружается через AppServiceProvider)
        $adminCountSettings = config('site_settings.AdminCountSettings', 10);

        return Inertia::render('Admin/Parameters/Index', [
            'settings' => SettingResource::collection($settings),
            'settingsCount' => $settingsCount,
            'adminCountSettings' => (int)$adminCountSettings,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): Response
    {
        return Inertia::render('Admin/Parameters/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(SettingRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $setting = Setting::create($data);

        // Log::info('Параметр системы создан: ', $setting->toArray());

        return redirect()->route('parameters.index')->with('success', 'Параметр системы успешно создан');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id): Response
    {

        $setting = Setting::findOrFail($id);

        return Inertia::render('Admin/Parameters/Edit', [
            'setting' => new SettingResource($setting),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(SettingRequest $request, string $id): RedirectResponse
    {
        $setting = Setting::findOrFail($id);
        $data = $request->validated();
        $setting->update($data);

        // Log::info('Параметр системы обновлен: ', $setting->toArray());

        return redirect()->route('parameters.index')->with('success', 'Параметр системы успешно обновлен');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id): RedirectResponse
    {
        $setting = Setting::findOrFail($id);
        $setting->delete();

        // Log::info('Параметр системы удален: ', $setting->toArray());

        return back();
    }

    /**
     * Обновление активности.
     */
    public function updateActivity(Request $request, $id): JsonResponse
    {
        $validated = $request->validate([
            'activity' => 'boolean',
        ]);

        $setting = Setting::findOrFail($id);
        $setting->activity = $validated['activity'];
        $setting->save();

        // Log::info("Обновлено activity параметра системы с ID: $id с данными: ", $validated);

        return response()->json(['success' => true, 'reload' => true]);
    }
}
