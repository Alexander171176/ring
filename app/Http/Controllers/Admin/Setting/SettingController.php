<?php

namespace App\Http\Controllers\Admin\Setting;

use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\Setting\SettingResource;
use App\Models\Admin\Setting\Setting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
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

//        Log::info('Полученные данные для обновления:', [
//            'color' => $validated['color'],
//            'opacity' => $validated['opacity']
//        ]);

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

        // Log::info('Настройки цвета и прозрачности панелей успешно обновлены.');

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

        Setting::updateOrCreate(
            ['option' => 'locale'],
            [
                'value'    => $validated['locale'],
                'type'     => 'string',
                'constant' => 'LOCALE',
                'category' => 'system',
                'activity' => true,
            ]
        );

        // Обновляем локаль для текущего запроса
        App::setLocale($validated['locale']);
        config(['app.locale' => $validated['locale']]);
        config(['site_settings.locale' => $validated['locale']]);

        // Если использовался отдельный кэш для локали, его можно сбросить
        Cache::forget('app_locale');

        return back()->with('success', 'Язык интерфейса успешно обновлен.');
    }

    /**
     * Обновляем количество рубрик в таблице на странице
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function updateAdminCountRubrics(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'value' => 'required|string', // меняем правило валидации на строку
        ]);

        $setting = Setting::where('option', 'AdminCountRubrics')->firstOrFail();
        $setting->value = $validated['value']; // если нужно, можно принудительно привести к строке: (string)$validated['value']
        $setting->save();

        // Обновляем конфигурацию, если нужно
        config(['site_settings.AdminCountRubrics' => $setting->value]);

        return response()->json(['success' => true, 'value' => $setting->value]);
    }

    /**
     * Обновляем количество секций в таблице на странице
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function updateAdminCountSections(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'value' => 'required|string', // меняем правило валидации на строку
        ]);

        $setting = Setting::where('option', 'AdminCountSections')->firstOrFail();
        $setting->value = $validated['value']; // если нужно, можно принудительно привести к строке: (string)$validated['value']
        $setting->save();

        // Обновляем конфигурацию, если нужно
        config(['site_settings.AdminCountSections' => $setting->value]);

        return response()->json(['success' => true, 'value' => $setting->value]);
    }

    /**
     * Обновляем количество постов в таблице на странице
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function updateAdminCountArticles(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'value' => 'required|string', // меняем правило валидации на строку
        ]);

        $setting = Setting::where('option', 'AdminCountArticles')->firstOrFail();
        $setting->value = $validated['value']; // если нужно, можно принудительно привести к строке: (string)$validated['value']
        $setting->save();

        // Обновляем конфигурацию, если нужно
        config(['site_settings.AdminCountArticles' => $setting->value]);

        return response()->json(['success' => true, 'value' => $setting->value]);
    }

}
