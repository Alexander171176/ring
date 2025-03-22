<?php

namespace App\Http\Controllers\Admin\Setting;

use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\Setting\SettingResource;
use App\Models\Admin\Setting\Setting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Illuminate\Support\Facades\Cache;
use Inertia\Response;

class SettingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): Response
    {
        $settings = Setting::all();
        $settingCount = DB::table('settings')->count();

        return Inertia::render('Admin/Settings/Index', [
            'settings' => SettingResource::collection($settings),
            'settingsCount' => $settingCount,
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

    /**
     * Получить настройки панели виджетов (цвет и прозрачность).
     */
    public function getWidgetPanelSettings(): JsonResponse
    {
        $colorSetting = Setting::where('option', 'widgetHexColor')->first();
        $opacitySetting = Setting::where('option', 'widgetOpacity')->first();

        $color = $colorSetting ? $colorSetting->value : '155e75';
        $opacity = $opacitySetting ? floatval($opacitySetting->value) : 0.99;

        return response()->json([
            'color' => $color,
            'opacity' => $opacity,
        ]);
    }

    /**
     * Обновить настройки панели виджетов (цвет и прозрачность).
     */
    public function updateWidgetPanelSettings(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'color' => 'required|string',
            'opacity' => 'required|numeric',
        ]);

        Log::info('Полученные данные для обновления:', [
            'color' => $validated['color'],
            'opacity' => $validated['opacity']
        ]);

        Setting::updateOrCreate(
            ['option' => 'widgetHexColor'],
            [
                'value' => $validated['color'],
                'type' => 'string',
                'constant' => 'WIDGET_HEX_COLOR',
                'category' => 'widget',
                'activity' => true
            ]
        );

        Setting::updateOrCreate(
            ['option' => 'widgetOpacity'],
            [
                'value' => $validated['opacity'],
                'type' => 'float',
                'constant' => 'WIDGET_OPACITY',
                'category' => 'widget',
                'activity' => true
            ]
        );

        Log::info('Настройки цвета и прозрачности панелей успешно обновлены.');

        return back()->with('success', 'Настройки цвета и прозрачности панелей успешно обновлены.');
    }

    /**
     * Получить текущий язык интерфейса.
     */
    public function getLocaleSetting(): JsonResponse
    {
        $localeSetting = Setting::where('option', 'locale')->first();
        $locale = $localeSetting ? $localeSetting->value : 'ru';

        return response()->json(['locale' => $locale]);
    }

    /**
     * Обновить язык интерфейса.
     */
    public function updateLocaleSetting(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'locale' => 'required|string|in:en,ru,kz',
        ]);

        Log::info('Полученный язык для обновления:', ['locale' => $validated['locale']]);

        Setting::updateOrCreate(
            ['option' => 'locale'],
            [
                'value' => $validated['locale'],
                'type' => 'string',
                'constant' => 'LOCALE',
                'category' => 'general',
                'activity' => true
            ]
        );

        // Очищаем кэш локали, если он использовался отдельно
        Cache::forget('app_locale');

        Log::info('Язык интерфейса успешно обновлен.');

        return back()->with('success', 'Язык интерфейса успешно обновлен.');
    }
}
