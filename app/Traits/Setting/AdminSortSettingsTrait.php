<?php

namespace App\Traits\Setting;

use App\Models\Admin\Setting\Setting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

trait AdminSortSettingsTrait
{
    /**
     * Обновить сортировку по умолчанию в таблице Рубрик.
     */
    public function updateAdminSortRubrics(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'value' => 'required|string',
        ]);

        $setting = Setting::where('option', 'AdminSortRubrics')->firstOrFail();
        $setting->value = $validated['value'];
        $setting->save();
        config(['site_settings.AdminSortRubrics' => $setting->value]);

        return response()->json(['success' => true, 'value' => $setting->value]);
    }

    /**
     * Обновить сортировку по умолчанию в таблице Секций.
     */
    public function updateAdminSortSections(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'value' => 'required|string',
        ]);

        $setting = Setting::where('option', 'AdminSortSections')->firstOrFail();
        $setting->value = $validated['value'];
        $setting->save();
        config(['site_settings.AdminSortSections' => $setting->value]);

        return response()->json(['success' => true, 'value' => $setting->value]);
    }

    /**
     * Обновить сортировку по умолчанию в таблице Постов.
     */
    public function updateAdminSortArticles(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'value' => 'required|string',
        ]);

        $setting = Setting::where('option', 'AdminSortArticles')->firstOrFail();
        $setting->value = $validated['value'];
        $setting->save();
        config(['site_settings.AdminSortArticles' => $setting->value]);

        return response()->json(['success' => true, 'value' => $setting->value]);
    }

    /**
     * Обновить сортировку по умолчанию в таблице Тегов.
     */
    public function updateAdminSortTags(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'value' => 'required|string',
        ]);

        $setting = Setting::where('option', 'AdminSortTags')->firstOrFail();
        $setting->value = $validated['value'];
        $setting->save();
        config(['site_settings.AdminSortTags' => $setting->value]);

        return response()->json(['success' => true, 'value' => $setting->value]);
    }

    /**
     * Обновить сортировку по умолчанию в таблице Комментариев.
     */
    public function updateAdminSortComments(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'value' => 'required|string',
        ]);

        $setting = Setting::where('option', 'AdminSortComments')->firstOrFail();
        $setting->value = $validated['value'];
        $setting->save();
        config(['site_settings.AdminSortComments' => $setting->value]);

        return response()->json(['success' => true, 'value' => $setting->value]);
    }

    /**
     * Обновить сортировку по умолчанию в таблице Баннеров.
     */
    public function updateAdminSortBanners(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'value' => 'required|string',
        ]);

        $setting = Setting::where('option', 'AdminSortBanners')->firstOrFail();
        $setting->value = $validated['value'];
        $setting->save();
        config(['site_settings.AdminSortBanners' => $setting->value]);

        return response()->json(['success' => true, 'value' => $setting->value]);
    }

    /**
     * Обновить сортировку по умолчанию в таблице Видео.
     */
    public function updateAdminSortVideos(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'value' => 'required|string',
        ]);

        $setting = Setting::where('option', 'AdminSortVideos')->firstOrFail();
        $setting->value = $validated['value'];
        $setting->save();
        config(['site_settings.AdminSortVideos' => $setting->value]);

        return response()->json(['success' => true, 'value' => $setting->value]);
    }

    /**
     * Обновить сортировку по умолчанию в таблице Пользователей.
     */
    public function updateAdminSortUsers(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'value' => 'required|string',
        ]);

        $setting = Setting::where('option', 'AdminSortUsers')->firstOrFail();
        $setting->value = $validated['value'];
        $setting->save();
        config(['site_settings.AdminSortUsers' => $setting->value]);

        return response()->json(['success' => true, 'value' => $setting->value]);
    }

    /**
     * Обновить сортировку по умолчанию в таблице Ролей.
     */
    public function updateAdminSortRoles(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'value' => 'required|string',
        ]);

        $setting = Setting::where('option', 'AdminSortRoles')->firstOrFail();
        $setting->value = $validated['value'];
        $setting->save();
        config(['site_settings.AdminSortRoles' => $setting->value]);

        return response()->json(['success' => true, 'value' => $setting->value]);
    }

    /**
     * Обновить сортировку по умолчанию в таблице Разрешений.
     */
    public function updateAdminSortPermissions(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'value' => 'required|string',
        ]);

        $setting = Setting::where('option', 'AdminSortPermissions')->firstOrFail();
        $setting->value = $validated['value'];
        $setting->save();
        config(['site_settings.AdminSortPermissions' => $setting->value]);

        return response()->json(['success' => true, 'value' => $setting->value]);
    }

    /**
     * Обновить сортировку по умолчанию в таблице Модулей.
     */
    public function updateAdminSortPlugins(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'value' => 'required|string',
        ]);

        $setting = Setting::where('option', 'AdminSortPlugins')->firstOrFail();
        $setting->value = $validated['value'];
        $setting->save();
        config(['site_settings.AdminSortPlugins' => $setting->value]);

        return response()->json(['success' => true, 'value' => $setting->value]);
    }

    /**
     * Обновить сортировку по умолчанию в таблице Параметров
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function updateAdminSortSettings(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'value' => 'required|string', // меняем правило валидации на строку
        ]);

        $setting = Setting::where('option', 'AdminSortSettings')->firstOrFail();
        $setting->value = $validated['value']; // если нужно, можно принудительно привести к строке: (string)$validated['value']
        $setting->save();

        // Обновляем конфигурацию, если нужно
        config(['site_settings.AdminSortSettings' => $setting->value]);

        return response()->json(['success' => true, 'value' => $setting->value]);
    }
}
