<?php

namespace App\Traits\Setting;

use App\Models\Admin\Setting\Setting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

trait AdminWidgetSettingsTrait
{
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

        Setting::updateOrCreate(
            ['option' => 'widgetHexColor'],
            [
                'value' => $validated['color'],
                'type' => 'string',
                'constant' => 'WIDGET_HEX_COLOR',
                'category' => 'widget',
                'activity' => true,
            ]
        );

        Setting::updateOrCreate(
            ['option' => 'widgetOpacity'],
            [
                'value' => $validated['opacity'],
                'type' => 'float',
                'constant' => 'WIDGET_OPACITY',
                'category' => 'widget',
                'activity' => true,
            ]
        );

        return back()->with('success', 'Настройки цвета и прозрачности панелей успешно обновлены.');
    }
}
