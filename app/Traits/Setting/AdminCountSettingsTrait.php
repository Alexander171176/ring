<?php

namespace App\Traits\Setting;

use App\Http\Requests\Admin\UpdateCountSettingRequest;
use App\Models\Admin\Setting\Setting;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Throwable;

// <--- Используем новый Request
// Добавляем Cache
// Добавляем Log
// Добавляем Throwable

trait AdminCountSettingsTrait
{
    /**
     * Общий метод для обновления настроек количества.
     */
    private function updateCountSetting(UpdateCountSettingRequest $request, string $optionKey, string $configKey): JsonResponse
    {
        // Авторизация и валидация в UpdateCountSettingRequest
        $validated = $request->validated();
        $newValue = $validated['value']; // Уже integer

        try {
            // Используем firstOrFail для поиска или ошибки 404
            $setting = Setting::where('option', $optionKey)->firstOrFail();
            $setting->value = (string)$newValue; // Сохраняем как строку, если БД ожидает строку
            // Или $setting->value = $newValue; если БД/каст обработают integer
            $setting->save();

            // Обновляем конфигурацию для текущего запроса
            config([$configKey => $newValue]);

            // Очищаем общий кэш настроек
            Cache::forget('site_settings'); // TODO: Использовать правильный ключ
            Log::info("Setting '{$optionKey}' updated to: " . $newValue);

            // Возвращаем успех и новое значение
            return response()->json(['success' => true, 'value' => $newValue]);

        } catch (Throwable $e) {
            Log::error("Ошибка обновления настройки '{$optionKey}': " . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Ошибка обновления настройки.'], 500);
        }
    }

    // Используем общий метод в публичных методах
    public function updateAdminCountRubrics(UpdateCountSettingRequest $request): JsonResponse
    { return $this->updateCountSetting($request, 'AdminCountRubrics', 'site_settings.AdminCountRubrics'); }

    public function updateAdminCountSections(UpdateCountSettingRequest $request): JsonResponse
    { return $this->updateCountSetting($request, 'AdminCountSections', 'site_settings.AdminCountSections'); }

    public function updateAdminCountArticles(UpdateCountSettingRequest $request): JsonResponse
    { return $this->updateCountSetting($request, 'AdminCountArticles', 'site_settings.AdminCountArticles'); }

    public function updateAdminCountTags(UpdateCountSettingRequest $request): JsonResponse
    { return $this->updateCountSetting($request, 'AdminCountTags', 'site_settings.AdminCountTags'); }

    public function updateAdminCountComments(UpdateCountSettingRequest $request): JsonResponse
    { return $this->updateCountSetting($request, 'AdminCountComments', 'site_settings.AdminCountComments'); }

    public function updateAdminCountBanners(UpdateCountSettingRequest $request): JsonResponse
    { return $this->updateCountSetting($request, 'AdminCountBanners', 'site_settings.AdminCountBanners'); }

    public function updateAdminCountVideos(UpdateCountSettingRequest $request): JsonResponse
    { return $this->updateCountSetting($request, 'AdminCountVideos', 'site_settings.AdminCountVideos'); }

    public function updateAdminCountUsers(UpdateCountSettingRequest $request): JsonResponse
    { return $this->updateCountSetting($request, 'AdminCountUsers', 'site_settings.AdminCountUsers'); }

    public function updateAdminCountRoles(UpdateCountSettingRequest $request): JsonResponse
    { return $this->updateCountSetting($request, 'AdminCountRoles', 'site_settings.AdminCountRoles'); }

    public function updateAdminCountPermissions(UpdateCountSettingRequest $request): JsonResponse
    { return $this->updateCountSetting($request, 'AdminCountPermissions', 'site_settings.AdminCountPermissions'); }

    public function updateAdminCountPlugins(UpdateCountSettingRequest $request): JsonResponse
    { return $this->updateCountSetting($request, 'AdminCountPlugins', 'site_settings.AdminCountPlugins'); }

    public function updateAdminCountSettings(UpdateCountSettingRequest $request): JsonResponse
    { return $this->updateCountSetting($request, 'AdminCountSettings', 'site_settings.AdminCountSettings'); }
}
