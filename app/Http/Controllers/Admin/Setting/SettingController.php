<?php

namespace App\Http\Controllers\Admin\Setting;

use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\Setting\SettingResource;
use App\Models\Admin\Setting\Setting;
use App\Traits\CacheTimeTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class SettingController extends Controller
{
    use CacheTimeTrait;

    /**
     * Display a listing of the resource.
     */
    public function index(): \Inertia\Response
    {
        $cacheTime = $this->getCacheTime();

        // Кэширование всех настроек
        $settings = Cache::tags(['settings'])->remember('settings.all', $cacheTime, function () {
            return Setting::all();
        });

        // Кэширование количества настроек
        $settingCount = Cache::tags(['settings'])->remember('settings.count', $cacheTime, function () {
            return DB::table('settings')->count();
        });

        return Inertia::render('Admin/Settings/Index', [
            'settings' => SettingResource::collection($settings),
            'settingsCount' => $settingCount,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id): \Illuminate\Http\RedirectResponse
    {
        $validated = $request->validate([
            'value' => 'required|string|max:255',
        ]);

        $setting = Setting::findOrFail($id);
        $setting->value = $validated['value'];
        $setting->save();

        // Очистка кэша после обновления настройки
        $this->clearCache(['settings']);

        // Перезагрузка страницы
        return back()->with('success', 'Настройка успешно обновлена');
    }

    /**
     * Получение настройки для времени простоя сайта.
     */
    public function getDowntimeSiteSetting(): \Illuminate\Http\JsonResponse
    {
        $cacheTime = $this->getCacheTime();

        $setting = Cache::tags(['settings'])->remember('setting.downtimeSite', $cacheTime, function () {
            return Setting::where('option', 'downtimeSite')->first();
        });

        $value = $setting ? $setting->value : 'false';
        return response()->json(['value' => $value]);
    }

    /**
     * Получить настройки панели виджетов (цвет и прозрачность).
     */
    public function getWidgetPanelSettings(): \Illuminate\Http\JsonResponse
    {
        $cacheTime = $this->getCacheTime();

        $colorSetting = Cache::tags(['settings'])->remember('setting.widgetHexColor', $cacheTime, function () {
            return Setting::where('option', 'widgetHexColor')->first();
        });

        $opacitySetting = Cache::tags(['settings'])->remember('setting.widgetOpacity', $cacheTime, function () {
            return Setting::where('option', 'widgetOpacity')->first();
        });

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
    public function updateWidgetPanelSettings(Request $request): \Illuminate\Http\RedirectResponse
    {
        $validated = $request->validate([
            'color' => 'required|string',
            'opacity' => 'required|numeric',
        ]);

        Log::info('Полученные данные для обновления:', ['color' => $validated['color'], 'opacity' => $validated['opacity']]);

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

        // Очистка кэша после обновления настроек панели виджетов
        $this->clearCache(['settings']);

        Log::info('Настройки цвета и прозрачности панелей успешно обновлены.');

        // Перезагрузка страницы
        return back()->with('success', 'Настройки цвета и прозрачности панелей успешно обновлены.');
    }

    /**
     * Получить текущий язык интерфейса.
     */
    public function getLocaleSetting(): \Illuminate\Http\JsonResponse
    {
        $cacheTime = $this->getCacheTime();

        $localeSetting = Cache::tags(['settings'])->remember('setting.locale', $cacheTime, function () {
            return Setting::where('option', 'locale')->first();
        });

        $locale = $localeSetting ? $localeSetting->value : 'ru'; // Язык по умолчанию — русский

        return response()->json(['locale' => $locale]);
    }

    /**
     * Обновить язык интерфейса.
     */
    public function updateLocaleSetting(Request $request): \Illuminate\Http\RedirectResponse
    {
        $validated = $request->validate([
            'locale' => 'required|string|in:en,ru', // допустимые значения языков
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

        // Очистка кэша после обновления настроек
        $this->clearCache(['settings']);

        Log::info('Язык интерфейса успешно обновлен.');

        // Перезагрузка страницы
        return back()->with('success', 'Язык интерфейса успешно обновлен.');
    }

}
