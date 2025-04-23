<?php

namespace App\Traits\Setting;

use App\Http\Requests\Admin\Setting\UpdateWidgetPanelRequest; // <--- Используем новый Request
use App\Models\Admin\Setting\Setting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse; // Используем RedirectResponse
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Throwable;

trait AdminWidgetSettingsTrait
{
    /**
     * Получить настройки панели виджетов (цвет и прозрачность).
     */
    public function getWidgetPanelSettings(): JsonResponse
    {
        // Используем кэширование
        $settings = Cache::remember('widget_panel_settings', 3600, function () {
            $color = Setting::where('option', 'widgetHexColor')->value('value') ?? '155E75';
            $opacity = Setting::where('option', 'widgetOpacity')->value('value') ?? 0.95;
            return ['color' => $color, 'opacity' => (float)$opacity];
        });

        return response()->json($settings);
    }

    /**
     * Обновить настройки панели виджетов (цвет и прозрачность).
     * Возвращаем RedirectResponse, т.к. вызывается из web.php, а не api.php
     */
    // Если этот метод вызывается ТОЛЬКО из API, то тип RedirectResponse нужно изменить на JsonResponse
    public function updateWidgetPanelSettings(UpdateWidgetPanelRequest $request): RedirectResponse // <--- Используем Form Request и RedirectResponse
    {
        // Авторизация и валидация в UpdateWidgetPanelRequest
        $validated = $request->validated();

        try {
            // Используем updateOrCreate
            Setting::updateOrCreate(
                ['option' => 'widgetHexColor'],
                [
                    'value' => $validated['color'], // Сохраняем HEX без #
                    'type' => 'string',
                    'constant' => 'WIDGET_HEX_COLOR', // TODO: Проверить/добавить константу
                    'category' => 'widget_panel', // TODO: Проверить/добавить категорию
                    'activity' => true,
                ]
            );

            Setting::updateOrCreate(
                ['option' => 'widgetOpacity'],
                [
                    'value' => $validated['opacity'], // Сохраняем число 0-1
                    'type' => 'float', // Тип float
                    'constant' => 'WIDGET_OPACITY', // TODO: Проверить/добавить константу
                    'category' => 'widget_panel', // TODO: Проверить/добавить категорию
                    'activity' => true,
                ]
            );

            // Очищаем кэши
            Cache::forget('widget_panel_settings');
            Cache::forget('site_settings'); // TODO: Использовать правильный ключ
            Log::info('Настройки панели виджетов обновлены', $validated);

            // Возвращаемся назад с сообщением об успехе
            return back()->with('success', 'Настройки панели виджетов успешно обновлены.');

        } catch (Throwable $e) {
            Log::error('Ошибка обновления настроек панели виджетов: ' . $e->getMessage());
            return back()->withInput()->withErrors(['general' => 'Ошибка сохранения настроек панели виджетов.']);
        }
    }
}
