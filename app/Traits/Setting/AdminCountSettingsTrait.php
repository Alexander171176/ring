<?php

namespace App\Traits\Setting;

use App\Models\Admin\Setting\Setting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

trait AdminCountSettingsTrait
{
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

    /**
     * Обновляем количество тегов в таблице на странице
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function updateAdminCountTags(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'value' => 'required|string', // меняем правило валидации на строку
        ]);

        $setting = Setting::where('option', 'AdminCountTags')->firstOrFail();
        $setting->value = $validated['value']; // если нужно, можно принудительно привести к строке: (string)$validated['value']
        $setting->save();

        // Обновляем конфигурацию, если нужно
        config(['site_settings.AdminCountTags' => $setting->value]);

        return response()->json(['success' => true, 'value' => $setting->value]);
    }

    /**
     * Обновляем количество комментариев в таблице на странице
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function updateAdminCountComments(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'value' => 'required|string', // меняем правило валидации на строку
        ]);

        $setting = Setting::where('option', 'AdminCountComments')->firstOrFail();
        $setting->value = $validated['value']; // если нужно, можно принудительно привести к строке: (string)$validated['value']
        $setting->save();

        // Обновляем конфигурацию, если нужно
        config(['site_settings.AdminCountComments' => $setting->value]);

        return response()->json(['success' => true, 'value' => $setting->value]);
    }

    /**
     * Обновляем количество баннеров в таблице на странице
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function updateAdminCountBanners(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'value' => 'required|string', // меняем правило валидации на строку
        ]);

        $setting = Setting::where('option', 'AdminCountBanners')->firstOrFail();
        $setting->value = $validated['value']; // если нужно, можно принудительно привести к строке: (string)$validated['value']
        $setting->save();

        // Обновляем конфигурацию, если нужно
        config(['site_settings.AdminCountBanners' => $setting->value]);

        return response()->json(['success' => true, 'value' => $setting->value]);
    }

    /**
     * Обновляем количество видео в таблице на странице
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function updateAdminCountVideos(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'value' => 'required|string', // меняем правило валидации на строку
        ]);

        $setting = Setting::where('option', 'AdminCountVideos')->firstOrFail();
        $setting->value = $validated['value']; // если нужно, можно принудительно привести к строке: (string)$validated['value']
        $setting->save();

        // Обновляем конфигурацию, если нужно
        config(['site_settings.AdminCountVideos' => $setting->value]);

        return response()->json(['success' => true, 'value' => $setting->value]);
    }

    /**
     * Обновляем количество пользователей в таблице на странице
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function updateAdminCountUsers(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'value' => 'required|string', // меняем правило валидации на строку
        ]);

        $setting = Setting::where('option', 'AdminCountUsers')->firstOrFail();
        $setting->value = $validated['value']; // если нужно, можно принудительно привести к строке: (string)$validated['value']
        $setting->save();

        // Обновляем конфигурацию, если нужно
        config(['site_settings.AdminCountUsers' => $setting->value]);

        return response()->json(['success' => true, 'value' => $setting->value]);
    }

    /**
     * Обновляем количество ролей в таблице на странице
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function updateAdminCountRoles(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'value' => 'required|string', // меняем правило валидации на строку
        ]);

        $setting = Setting::where('option', 'AdminCountRoles')->firstOrFail();
        $setting->value = $validated['value']; // если нужно, можно принудительно привести к строке: (string)$validated['value']
        $setting->save();

        // Обновляем конфигурацию, если нужно
        config(['site_settings.AdminCountRoles' => $setting->value]);

        return response()->json(['success' => true, 'value' => $setting->value]);
    }

    /**
     * Обновляем количество разрешений в таблице на странице
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function updateAdminCountPermissions(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'value' => 'required|string', // меняем правило валидации на строку
        ]);

        $setting = Setting::where('option', 'AdminCountPermissions')->firstOrFail();
        $setting->value = $validated['value']; // если нужно, можно принудительно привести к строке: (string)$validated['value']
        $setting->save();

        // Обновляем конфигурацию, если нужно
        config(['site_settings.AdminCountPermissions' => $setting->value]);

        return response()->json(['success' => true, 'value' => $setting->value]);
    }


    /**
     * Обновляем количество модулей в таблице на странице
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function updateAdminCountPlugins(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'value' => 'required|string', // меняем правило валидации на строку
        ]);

        $setting = Setting::where('option', 'AdminCountPlugins')->firstOrFail();
        $setting->value = $validated['value']; // если нужно, можно принудительно привести к строке: (string)$validated['value']
        $setting->save();

        // Обновляем конфигурацию, если нужно
        config(['site_settings.AdminCountPlugins' => $setting->value]);

        return response()->json(['success' => true, 'value' => $setting->value]);
    }

    /**
     * Обновляем количество параметров в таблице на странице
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function updateAdminCountSettings(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'value' => 'required|string', // меняем правило валидации на строку
        ]);

        $setting = Setting::where('option', 'AdminCountSettings')->firstOrFail();
        $setting->value = $validated['value']; // если нужно, можно принудительно привести к строке: (string)$validated['value']
        $setting->save();

        // Обновляем конфигурацию, если нужно
        config(['site_settings.AdminCountSettings' => $setting->value]);

        return response()->json(['success' => true, 'value' => $setting->value]);
    }
}
