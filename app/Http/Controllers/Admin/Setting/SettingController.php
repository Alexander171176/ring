<?php

namespace App\Http\Controllers\Admin\Setting;

use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\Setting\SettingResource;
use App\Models\Admin\Setting\Setting;
use App\Traits\Setting\AdminCountSettingsTrait;
use App\Traits\Setting\AdminSortSettingsTrait;
use App\Traits\Setting\AdminWidgetSettingsTrait;
use App\Traits\Setting\LocaleSettingsTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response;

class SettingController extends Controller
{
    use LocaleSettingsTrait, AdminWidgetSettingsTrait, AdminCountSettingsTrait, AdminSortSettingsTrait;

    /**
     * Display a listing of the resource.
     */
    public function index(): Response
    {
        $settings = Setting::all();
        $settingCount = DB::table('settings')->count();

        // Получаем значение параметра из конфигурации (оно загружается через AppServiceProvider)
        $adminCountSettings = config('site_settings.AdminCountSettings', 10);
        $adminSortSettings  = config('site_settings.AdminSortSettings', 'idDesc');

        return Inertia::render('Admin/Settings/Index', [
            'settings' => SettingResource::collection($settings),
            'settingsCount' => $settingCount,
            'adminCountSettings' => (int)$adminCountSettings,
            'adminSortSettings' => $adminSortSettings,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id): RedirectResponse
    {
        $validated = $request->validate([
            'value' => 'required|string|max:255',
        ]);

        $setting = Setting::findOrFail($id);
        $setting->value = $validated['value'];
        $setting->save();

        return back()->with('success', 'Настройка успешно обновлена');
    }

    /**
     * Получение настройки для времени простоя сайта.
     */
    public function getDowntimeSiteSetting(): JsonResponse
    {
        $setting = Setting::where('option', 'downtimeSite')->first();
        $value = $setting ? $setting->value : 'false';
        return response()->json(['value' => $value]);
    }

}
