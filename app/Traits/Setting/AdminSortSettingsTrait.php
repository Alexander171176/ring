<?php
namespace App\Traits\Setting;

// Импортируем ВСЕ специфичные реквесты ТОЛЬКО для доступа к правилам
use App\Http\Requests\Admin\Rubric\UpdateSortRubricRequest;
use App\Http\Requests\Admin\Section\UpdateSortSectionRequest;
// ... и другие ...
// И ОБЩИЙ реквест для настроек по умолчанию
use App\Http\Requests\Admin\Setting\UpdateSortSettingRequest;

use App\Models\Admin\Setting\Setting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request; // <--- Принимаем базовый Request
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Throwable;
use Illuminate\Auth\Access\AuthorizationException;

trait AdminSortSettingsTrait
{
    /**
     * Приватный метод для валидации (с помощью правил из FormRequest) и сохранения.
     */
    private function validateAndSaveSortSetting(Request $request, string $formRequestClass, string $optionKey, string $configKey): JsonResponse
    {
        // 1. Проверяем авторизацию вручную
        try {
            /** @var \Illuminate\Foundation\Http\FormRequest $specificRequestInstance */
            $specificRequestInstance = app($formRequestClass);
            $specificRequestInstance->setUserResolver(fn() => $request->user());
            if (method_exists($specificRequestInstance, 'authorize') && !$specificRequestInstance->authorize()) {
                throw new AuthorizationException('Действие не авторизовано.');
            }
        } catch (AuthorizationException $e){
            return response()->json(['message' => $e->getMessage() ?: 'Действие не авторизовано.'], 403);
        } catch (Throwable $e) { /* ... обработка ошибки авторизации ... */ }

        // 2. Валидируем данные
        $validator = Validator::make(
            $request->all(),
            $specificRequestInstance->rules(), // Правила из нужного реквеста
            $specificRequestInstance->messages() // Сообщения из нужного реквеста
        );

        if ($validator->fails()) {
            return response()->json([ 'message' => 'Переданные данные неверны.', 'errors' => $validator->errors() ], 422);
        }
        $validated = $validator->validated();
        $newValue = $validated['value'];

        // 3. Сохраняем настройку
        try {
            $setting = Setting::updateOrCreate(['option' => $optionKey], ['value' => $newValue, /*...*/ 'activity' => true]);
            config([$configKey => $newValue]);
            $this->clearSettingsCache(); // Вызываем метод очистки кэша (он должен быть доступен)
            Log::info("Setting '{$optionKey}' updated to: " . $newValue . " by User ID: " . $request->user()?->id);
            return response()->json(['success' => true, 'value' => $newValue]);
        } catch (Throwable $e) { /* ... обработка ошибок сохранения ... */ }
    }

    // --- Публичные методы ---
    public function updateAdminSortRubrics(Request $request): JsonResponse
    { return $this->validateAndSaveSortSetting($request, UpdateSortRubricRequest::class, 'AdminSortRubrics', 'site_settings.AdminSortRubrics'); }

    public function updateAdminSortSections(Request $request): JsonResponse
    { return $this->validateAndSaveSortSetting($request, UpdateSortSectionRequest::class, 'AdminSortSections', 'site_settings.AdminSortSections'); }

    // ... и так далее для других, передавая ::class нужного реквеста ...

    // Для общих настроек используем общий реквест
    public function updateAdminSortSettings(Request $request): JsonResponse
    { return $this->validateAndSaveSortSetting($request, UpdateSortSettingRequest::class, 'AdminSortSettings', 'site_settings.AdminSortSettings'); }


    /**
     * Приватный метод для очистки кэша (если он не доступен из контроллера).
     * Скопируйте или сделайте хелпер.
     */
    private function clearSettingsCache(string $specificKey = null): void {
        // TODO: Использовать ваши реальные ключи кэша
        Cache::forget('site_settings');
        Cache::forget('setting_locale');
        Cache::forget('widget_panel_settings');
        Cache::forget('sidebar_settings');
        if ($specificKey) { Cache::forget($specificKey); }
        Log::debug("Settings cache cleared by AdminSortSettingsTrait.", ['specific_key' => $specificKey]);
    }
}
