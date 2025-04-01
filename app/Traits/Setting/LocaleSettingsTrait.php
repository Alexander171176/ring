<?php

namespace App\Traits\Setting;

use App\Models\Admin\Setting\Setting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cache;

trait LocaleSettingsTrait
{
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
                'value' => $validated['locale'],
                'type' => 'string',
                'constant' => 'LOCALE',
                'category' => 'system',
                'activity' => true,
            ]
        );

        App::setLocale($validated['locale']);
        config(['app.locale' => $validated['locale']]);
        config(['site_settings.locale' => $validated['locale']]);
        Cache::forget('app_locale');

        return back()->with('success', 'Язык интерфейса успешно обновлен.');
    }
}
