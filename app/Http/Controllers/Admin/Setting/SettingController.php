<?php

namespace App\Http\Controllers\Admin\Setting;

use App\Http\Controllers\Controller;
// Реквесты
use App\Http\Requests\Admin\Setting\SettingRequest;
use App\Http\Requests\Admin\Setting\UpdateLocaleRequest;
use App\Http\Requests\Admin\Setting\UpdateSortSettingRequest;
use App\Http\Requests\Admin\Setting\UpdateWidgetPanelRequest;
use App\Http\Requests\Admin\UpdateActivityRequest;
use App\Http\Requests\Admin\UpdateCountSettingRequest;
use App\Http\Requests\Admin\UpdateSortRequest;
// Ресурсы
use App\Http\Resources\Admin\Setting\SettingResource;
// Модели
use App\Models\Admin\Setting\Setting;
// Фасады и прочее
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;
use Throwable;

class SettingController extends Controller
{
    // Время кэширования специфичных настроек
    private const SETTINGS_CACHE_TTL = 3600; // 1 час

    // --- Стандартные CRUD методы ---

    /**
     * Отображение списка всех настроек.
     *
     * @return InertiaResponse
     */
    public function index(): InertiaResponse
    {
        // TODO: Проверка прав $this->authorize('view-settings', Setting::class);

        // Получаем настройки для фронтенда (дефолтные значения)
        $adminCountSettings = config('site_settings.AdminCountSettings', 15); // Для ItemsPerPageSelect
        $adminSortSettings  = config('site_settings.AdminSortSettings', 'idDesc'); // Для SortSelect

        try {
            // Загружаем ВСЕ рубрики с количеством секций (или без, если не нужно в таблице)
            $settings = Setting::all(); // Загружаем ВСЕ
            $settingsCount = $settings->count(); // Считаем из загруженной коллекции

        } catch (Throwable $e) {
            Log::error("Ошибка загрузки рубрик для Index: " . $e->getMessage());
            $settings = collect();
            $settingsCount = 0;
            session()->flash('error', 'Не удалось загрузить список параметров.');
        }

        return Inertia::render('Admin/Settings/Index', [
            // Передаем ПОЛНУЮ коллекцию ресурсов
            'settings' => SettingResource::collection($settings),
            'settingsCount' => $settingsCount,
            // Передаем дефолтные/текущие настройки для инициализации фронтенда
            'adminCountSettings' => (int)$adminCountSettings,
            'adminSortSettings' => $adminSortSettings, // Это значение прочитает SortSelect при загрузке
        ]);
    }

    /**
     * Показ формы создания настройки.
     *
     * @return InertiaResponse
     */
    public function create(): InertiaResponse
    {
        // TODO: Проверка прав $this->authorize('create-setting', Setting::class);
        return Inertia::render('Admin/Settings/Create');
    }

    /**
     * Создание новой настройки.
     *
     * @param SettingRequest $request
     * @return RedirectResponse
     */
    public function store(SettingRequest $request): RedirectResponse
    {
        $data = $request->validated();
        try {
            DB::beginTransaction();
            Setting::create($data);
            $this->clearSettingsCache();
            DB::commit();
            Log::info('Настройка успешно создана: ', ['option' => $data['option']]);
            return redirect()->route('admin.settings.index')->with('success', 'Настройка успешно создана.');
        } catch (Throwable $e) {
            DB::rollBack();
            Log::error("Ошибка при создании настройки: " . $e->getMessage());
            return back()->withInput()->withErrors(['general' => 'Произошла ошибка при создании настройки.']);
        }
    }

    /**
     * Показ формы редактирования настройки.
     *
     * @param Setting $setting
     * @return InertiaResponse
     */
    public function edit(Setting $setting): InertiaResponse // Используем RMB
    {
        // TODO: Проверка прав $this->authorize('update-setting', $setting);
        return Inertia::render('Admin/Settings/Edit', [
            'setting' => new SettingResource($setting),
        ]);
    }

    /**
     * Обновление существующей настройки (всех полей).
     *
     * @param SettingRequest $request
     * @param Setting $setting
     * @return RedirectResponse
     */
    public function update(SettingRequest $request, Setting $setting): RedirectResponse // Используем RMB и SettingRequest
    {
        $data = $request->validated();
        try {
            DB::beginTransaction();
            $setting->update($data);
            $this->clearSettingsCache('setting_' . $setting->option);
            DB::commit();
            Log::info('Настройка обновлена: ', ['id' => $setting->id, 'option' => $setting->option]);
            return redirect()->route('admin.settings.index')->with('success', 'Настройка успешно обновлена.');
        } catch (Throwable $e) {
            DB::rollBack();
            Log::error("Ошибка при обновлении настройки ID {$setting->id}: " . $e->getMessage());
            return back()->withInput()->withErrors(['general' => 'Произошла ошибка при обновлении настройки.']);
        }
    }

    /**
     * Удаление настройки.
     *
     * @param Setting $setting
     * @return RedirectResponse
     */
    public function destroy(Setting $setting): RedirectResponse // Используем RMB
    {
        // TODO: Проверка прав $this->authorize('delete-setting', $setting);
        try {
            DB::beginTransaction();
            $optionKey = $setting->option;
            $setting->delete();
            $this->clearSettingsCache('setting_' . $optionKey);
            DB::commit();
            Log::info('Настройка удалена: ID ' . $setting->id);
            return redirect()->route('admin.settings.index')->with('success', 'Настройка успешно удалена.');
        } catch (Throwable $e) {
            DB::rollBack();
            Log::error("Ошибка при удалении настройки ID {$setting->id}: " . $e->getMessage());
            return back()->withErrors(['general' => 'Произошла ошибка при удалении настройки.']);
        }
    }

    /**
     * Массовое удаление настроек.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function bulkDestroy(Request $request): JsonResponse
    {
        // TODO: Проверка прав $this->authorize('delete-bulk settings');
        $validated = $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'required|integer|exists:settings,id',
        ]);
        $settingIds = $validated['ids'];
        try {
            DB::beginTransaction();
            $optionsToClear = Setting::whereIn('id', $settingIds)->pluck('option');
            Setting::whereIn('id', $settingIds)->delete();
            foreach ($optionsToClear as $option) { $this->clearSettingsCache('setting_' . $option); }
            $this->clearSettingsCache();
            DB::commit();
            Log::info('Настройки удалены: ', $settingIds);
            return response()->json(['success' => true, 'message' => 'Выбранные настройки удалены.', 'reload' => true]);
        } catch (Throwable $e) {
            DB::rollBack();
            Log::error("Ошибка при массовом удалении настроек: " . $e->getMessage(), ['ids' => $settingIds]);
            return response()->json(['success' => false, 'message' => 'Ошибка при удалении настроек.'], 500);
        }
    }

    /**
     * Обновление активности Настройки.
     *
     * @param UpdateActivityRequest $request
     * @param Setting $setting
     * @return JsonResponse
     */
    // Используем {setting} в маршруте для RMB
    public function updateActivity(UpdateActivityRequest $request, Setting $setting): JsonResponse
    {
        // authorize() в UpdateActivityRequest
        $validated = $request->validated();
        try {
            $setting->activity = $validated['activity'];
            $setting->save();
            $this->clearSettingsCache('setting_' . $setting->option);
            Log::info("Обновлено activity настройки ID {$setting->id} на {$setting->activity}");
            return response()->json(['success' => true, 'reload' => true]);
        } catch (Throwable $e) {
            Log::error("Ошибка обновления активности настройки ID {$setting->id}: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Ошибка обновления активности.'], 500);
        }
    }

    /**
     * Получить текущую активную локаль сайта (API).
     * Используется маршрутом GET /settings/locale.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getLocaleSetting(Request $request): JsonResponse // Добавляем Request
    {
        // Можно не проверять права для простого GET запроса на локаль,
        // но если нужно, добавьте $this->authorize(...)
        try {
            $locale = Cache::remember('setting_locale', self::SETTINGS_CACHE_TTL, function () {
                return Setting::where('option', 'locale')
                    // ->where('activity', true) // Учитываем активность настройки?
                    ->value('value') ?? config('app.fallback_locale', 'ru');
            });

            // Очистка и валидация полученного значения
            $cleanLocale = trim($locale ?? '', '"\' ');
            if (!preg_match('/^[a-z]{2}(?:[_-][A-Z]{2})?$/i', $cleanLocale)) {
                Log::warning("Invalid locale value retrieved from cache/db for 'locale', using fallback.", ['retrieved_value' => $locale]);
                $cleanLocale = config('app.fallback_locale', 'ru');
            }

            return response()->json(['locale' => $cleanLocale]);

        } catch (Throwable $e) {
            Log::error("Ошибка получения настройки локали: " . $e->getMessage());
            // Возвращаем дефолтную локаль в случае ошибки
            return response()->json(['locale' => config('app.fallback_locale', 'ru')], 500);
        }
    }

    /**
     * Обновить язык интерфейса В СЕССИИ пользователя/гостя (НЕ в базе данных).
     * Используется маршрутом POST /settings/locale.
     * Возвращает RedirectResponse, т.к. вызывается из web.php (вероятно, формой).
     *
     * @param UpdateLocaleRequest $request
     * @return RedirectResponse
     */
    public function updateLocaleSetting(UpdateLocaleRequest $request): RedirectResponse // Используем UpdateLocaleRequest
    {
        // Авторизация теперь в UpdateLocaleRequest (должна возвращать true для всех)
        $validated = $request->validated();
        $newLocale = $validated['locale'];

        try {
            // Устанавливаем локаль для ТЕКУЩЕГО запроса
            App::setLocale($newLocale);

            // Сохраняем выбранную локаль В СЕССИЮ
            Session::put('locale', $newLocale);
            Log::info("User/Guest updated session locale to: " . $newLocale);

            // Очищаем кэши, которые могут зависеть от локали
            // TODO: Определить и очистить нужные кэши, если они есть и зависят от локали пользователя
            // $this->clearSettingsCache('setting_locale'); // Очистка кэша самой настройки здесь не нужна

            // Возвращаемся на предыдущую страницу
            // Можно добавить сообщение об успехе, которое покажет ваш фронтенд
            return back()->with('success', 'Язык успешно изменен.')->with('locale_updated', true);

        } catch (Throwable $e) {
            Log::error("Ошибка при установке локали в сессию: " . $e->getMessage());
            return back()->withInput()->withErrors(['general' => 'Произошла ошибка при смене языка.']);
        }
    }

    // --- Приватный Хелпер для Обновления Настроек Count и Sort ---

    /**
     * Обновляет настройку и возвращает RedirectResponse.
     *
     * @param FormRequest $request Валидированный запрос (UpdateCountSettingRequest или UpdateSortSettingRequest)
     * @param string $optionKey Ключ опции в БД (напр., 'AdminCountRubrics')
     * @param string $configKey Ключ в runtime конфиге (напр., 'site_settings.AdminCountRubrics')
     * @param string $settingType Тип значения для поля 'type' в БД ('number' или 'string')
     * @param string $settingCategory Категория настройки в БД ('admin_display' или 'admin_sort')
     * @param string $successMessage Сообщение для flash об успехе
     * @param string $errorMessage Сообщение для flash об ошибке
     * @return RedirectResponse
     */
    private function updateSettingAndRedirect(
        FormRequest $request, // Принимаем базовый FormRequest, т.к. конкретный уже отработал
        string $optionKey,
        string $configKey,
        string $settingType,
        string $settingCategory,
        string $successMessage,
        string $errorMessage
    ): RedirectResponse
    {
        // Данные уже валидированы специфичным реквестом (UpdateCount... или UpdateSort...)
        $validated = $request->validated();
        $newValue = $validated['value']; // Получаем валидированное значение

        try {
            // Транзакция для updateOrCreate не строго обязательна, но может быть для консистентности
            DB::beginTransaction();

            Setting::updateOrCreate(
                ['option' => $optionKey], // Условие поиска
                [                       // Данные для обновления/создания
                    'value' => (string)$newValue, // Сохраняем всегда как строку
                    'type' => $settingType,
                    'constant' => strtoupper($optionKey), // Генерируем константу
                    'category' => $settingCategory,
                    'activity' => true,
                ]
            );

            config([$configKey => $newValue]); // Обновляем runtime конфиг
            $this->clearSettingsCache('setting_' . $optionKey); // Очищаем кэш
            $this->clearSettingsCache();

            DB::commit(); // Если используем транзакцию

            Log::info("Настройка '{$optionKey}' обновлена на: " . $newValue . " пользователем ID: " . $request->user()?->id);
            return back()->with('success', $successMessage); // Возвращаем универсальное сообщение

        } catch (Throwable $e) {
            DB::rollBack(); // Если используем транзакцию
            Log::error("Ошибка обновления настройки '{$optionKey}': " . $e->getMessage());
            return back()->withInput()->withErrors(['value' => $errorMessage]); // Возвращаем универсальное сообщение
        }
    }

    // Публичные методы для показа количества строк (принимают общий UpdateCountSettingRequest)

    /**
     * Обновление количества элементов в настройках
     *
     * @param UpdateCountSettingRequest $request
     * @return RedirectResponse
     */
    public function updateAdminCountSettings(UpdateCountSettingRequest $request): RedirectResponse
    {
        return $this->updateSettingAndRedirect(
            $request,
            'AdminCountSettings',
            'site_settings.AdminCountSettings',
            'number',
            'admin_display',
            'Количество элементов на странице успешно обновлено.',
            'Ошибка обновления настройки количества элементов.'
        );
    }

    /**
     * Обновление количества элементов в рубриках
     *
     * @param UpdateCountSettingRequest $request
     * @return RedirectResponse
     */
    public function updateAdminCountRubrics(UpdateCountSettingRequest $request): RedirectResponse
    {
        return $this->updateSettingAndRedirect(
            $request,
            'AdminCountRubrics',
            'site_settings.AdminCountRubrics',
            'number',
            'admin_display',
            'Количество элементов на странице успешно обновлено.',
            'Ошибка обновления настройки количества элементов.'
        );
    }

    /**
     * Обновление количества элементов в секциях
     *
     * @param UpdateCountSettingRequest $request
     * @return RedirectResponse
     */
    public function updateAdminCountSections(UpdateCountSettingRequest $request): RedirectResponse
    {
        return $this->updateSettingAndRedirect(
            $request,
            'AdminCountSections',
            'site_settings.AdminCountSections',
            'number', 'admin_display',
            'Количество элементов на странице успешно обновлено.',
            'Ошибка обновления настройки количества элементов.'
        );
    }

    /**
     * Обновление количества элементов в статьях
     *
     * @param UpdateCountSettingRequest $request
     * @return RedirectResponse
     */
    public function updateAdminCountArticles(UpdateCountSettingRequest $request): RedirectResponse
    {
        return $this->updateSettingAndRedirect(
            $request,
            'AdminCountArticles',
            'site_settings.AdminCountArticles',
            'number',
            'admin_display',
            'Количество элементов на странице успешно обновлено.',
            'Ошибка обновления настройки количества элементов.'
        );
    }

    /**
     * Обновление количества элементов в тегах
     *
     * @param UpdateCountSettingRequest $request
     * @return RedirectResponse
     */
    public function updateAdminCountTags(UpdateCountSettingRequest $request): RedirectResponse
    {
        return $this->updateSettingAndRedirect(
            $request,
            'AdminCountTags',
            'site_settings.AdminCountTags',
            'number', 'admin_display',
            'Количество элементов на странице успешно обновлено.',
            'Ошибка обновления настройки количества элементов.');
    }

    /**
     * Обновление количества элементов в комментариях
     *
     * @param UpdateCountSettingRequest $request
     * @return RedirectResponse
     */
    public function updateAdminCountComments(UpdateCountSettingRequest $request): RedirectResponse
    {
        return $this->updateSettingAndRedirect(
            $request,
            'AdminCountComments',
            'site_settings.AdminCountComments',
            'number',
            'admin_display',
            'Количество элементов на странице успешно обновлено.',
            'Ошибка обновления настройки количества элементов.');
    }

    /**
     * Обновление количества элементов в баннерах
     *
     * @param UpdateCountSettingRequest $request
     * @return RedirectResponse
     */
    public function updateAdminCountBanners(UpdateCountSettingRequest $request): RedirectResponse
    {
        return $this->updateSettingAndRedirect(
            $request,
            'AdminCountBanners',
            'site_settings.AdminCountBanners',
            'number',
            'admin_display',
            'Количество элементов на странице успешно обновлено.',
            'Ошибка обновления настройки количества элементов.'
        );
    }

    /**
     * Обновление количества элементов в видео
     *
     * @param UpdateCountSettingRequest $request
     * @return RedirectResponse
     */
    public function updateAdminCountVideos(UpdateCountSettingRequest $request): RedirectResponse
    {
        return $this->updateSettingAndRedirect(
            $request,
            'AdminCountVideos',
            'site_settings.AdminCountVideos',
            'number',
            'admin_display',
            'Количество элементов на странице успешно обновлено.',
            'Ошибка обновления настройки количества элементов.'
        );
    }

    /**
     * Обновление количества элементов в пользователях
     *
     * @param UpdateCountSettingRequest $request
     * @return RedirectResponse
     */
    public function updateAdminCountUsers(UpdateCountSettingRequest $request): RedirectResponse
    {
        return $this->updateSettingAndRedirect(
            $request,
            'AdminCountUsers',
            'site_settings.AdminCountUsers',
            'number',
            'admin_display',
            'Количество элементов на странице успешно обновлено.',
            'Ошибка обновления настройки количества элементов.'
        );
    }

    /**
     * Обновление количества элементов в ролях
     *
     * @param UpdateCountSettingRequest $request
     * @return RedirectResponse
     */
    public function updateAdminCountRoles(UpdateCountSettingRequest $request): RedirectResponse
    {
        return $this->updateSettingAndRedirect(
            $request,
            'AdminCountRoles',
            'site_settings.AdminCountRoles',
            'number',
            'admin_display',
            'Количество элементов на странице успешно обновлено.',
            'Ошибка обновления настройки количества элементов.'
        );
    }

    /**
     * Обновление количества элементов в разрешениях
     *
     * @param UpdateCountSettingRequest $request
     * @return RedirectResponse
     */
    public function updateAdminCountPermissions(UpdateCountSettingRequest $request): RedirectResponse
    {
        return $this->updateSettingAndRedirect(
            $request,
            'AdminCountPermissions',
            'site_settings.AdminCountPermissions',
            'number',
            'admin_display',
            'Количество элементов на странице успешно обновлено.',
            'Ошибка обновления настройки количества элементов.'
        );
    }

    /**
     * Обновление количества элементов в модулях
     *
     * @param UpdateCountSettingRequest $request
     * @return RedirectResponse
     */
    public function updateAdminCountPlugins(UpdateCountSettingRequest $request): RedirectResponse
    {
        return $this->updateSettingAndRedirect(
            $request,
            'AdminCountPlugins',
            'site_settings.AdminCountPlugins',
            'number',
            'admin_display',
            'Количество элементов на странице успешно обновлено.',
            'Ошибка обновления настройки количества элементов.'
        );
    }

    // Публичные методы для сортировки (принимают общий UpdateSortRequest)

    /**
     * Обновляет сортировку элементов в настройках
     *
     * @param UpdateSortRequest $request
     * @return RedirectResponse
     */
    public function updateAdminSortSettings(UpdateSortRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $newValue = $validated['value'];
        $optionKey = 'AdminSortSettings'; // Ключ опции специфичен для метода
        $configKey = 'site_settings.AdminSortSettings'; // Ключ конфига специфичен

        try {
            Setting::updateOrCreate(
                ['option' => $optionKey],
                [
                    'value' => $newValue,
                    'type' => 'string',
                    'constant' => strtoupper($optionKey),
                    'category' => 'admin_sort',
                    'activity' => true,
                ]
            );

            config([$configKey => $newValue]);
            $this->clearSettingsCache('setting_' . $optionKey);
            $this->clearSettingsCache();

            Log::info("Настройка '{$optionKey}' обновлена на: " . $newValue . " пользователем ID: " . $request->user()?->id);
            return back()->with('success', "Сортировка по умолчанию успешно обновлена."); // Универсальное сообщение

        } catch (Throwable $e) {
            Log::error("Ошибка обновления настройки сортировки '{$optionKey}': " . $e->getMessage());
            return back()->withInput()->withErrors(['value' => "Ошибка обновления настройки сортировки."]); // Универсальное сообщение
        }
    }

    /**
     * Обновляет сортировку элементов в рубриках
     *
     * @param UpdateSortRequest $request
     * @return RedirectResponse
     */
    public function updateAdminSortRubrics(UpdateSortRequest $request): RedirectResponse
    {
        return $this->updateSettingAndRedirect(
            $request,
            'AdminSortRubrics',
            'site_settings.AdminSortRubrics',
            'string',
            'admin_sort',
            'Сортировка по умолчанию успешно обновлена.',
            'Ошибка обновления настройки сортировки.'
        );
    }

    /**
     * Обновляет сортировку элементов в секциях
     *
     * @param UpdateSortRequest $request
     * @return RedirectResponse
     */
    public function updateAdminSortSections(UpdateSortRequest $request): RedirectResponse
    {
        return $this->updateSettingAndRedirect(
            $request,
            'AdminSortSections',
            'site_settings.AdminSortSections',
            'string',
            'admin_sort',
            'Сортировка по умолчанию успешно обновлена.',
            'Ошибка обновления настройки сортировки.'
        );
    }

    /**
     * Обновляет сортировку элементов в статьях
     *
     * @param UpdateSortRequest $request
     * @return RedirectResponse
     */
    public function updateAdminSortArticles(UpdateSortRequest $request): RedirectResponse
    {
        return $this->updateSettingAndRedirect(
            $request,
            'AdminSortArticles',
            'site_settings.AdminSortArticles',
            'string',
            'admin_sort',
            'Сортировка по умолчанию успешно обновлена.',
            'Ошибка обновления настройки сортировки.'
        );
    }

    /**
     * Обновляет сортировку элементов в тегах
     *
     * @param UpdateSortRequest $request
     * @return RedirectResponse
     */
    public function updateAdminSortTags(UpdateSortRequest $request): RedirectResponse
    {
        return $this->updateSettingAndRedirect(
            $request,
            'AdminSortTags',
            'site_settings.AdminSortTags',
            'string',
            'admin_sort',
            'Сортировка по умолчанию успешно обновлена.',
            'Ошибка обновления настройки сортировки.'
        );
    }

    /**
     * Обновляет сортировку элементов в комментариях
     *
     * @param UpdateSortRequest $request
     * @return RedirectResponse
     */
    public function updateAdminSortComments(UpdateSortRequest $request): RedirectResponse
    {
        return $this->updateSettingAndRedirect(
            $request,
            'AdminSortComments',
            'site_settings.AdminSortComments',
            'string',
            'admin_sort',
            'Сортировка по умолчанию успешно обновлена.',
            'Ошибка обновления настройки сортировки.'
        );
    }

    /**
     * Обновляет сортировку элементов в баннерах
     *
     * @param UpdateSortRequest $request
     * @return RedirectResponse
     */
    public function updateAdminSortBanners(UpdateSortRequest $request): RedirectResponse
    {
        return $this->updateSettingAndRedirect(
            $request,
            'AdminSortBanners',
            'site_settings.AdminSortBanners',
            'string',
            'admin_sort',
            'Сортировка по умолчанию успешно обновлена.',
            'Ошибка обновления настройки сортировки.'
        );
    }

    /**
     * Обновляет сортировку элементов в видео
     *
     * @param UpdateSortRequest $request
     * @return RedirectResponse
     */
    public function updateAdminSortVideos(UpdateSortRequest $request): RedirectResponse
    {
        return $this->updateSettingAndRedirect(
            $request,
            'AdminSortVideos',
            'site_settings.AdminSortVideos',
            'string',
            'admin_sort',
            'Сортировка по умолчанию успешно обновлена.',
            'Ошибка обновления настройки сортировки.'
        );
    }

    /**
     * Обновляет сортировку элементов в пользователях
     *
     * @param UpdateSortRequest $request
     * @return RedirectResponse
     */
    public function updateAdminSortUsers(UpdateSortRequest $request): RedirectResponse
    {
        return $this->updateSettingAndRedirect(
            $request,
            'AdminSortUsers',
            'site_settings.AdminSortUsers',
            'string',
            'admin_sort',
            'Сортировка по умолчанию успешно обновлена.',
            'Ошибка обновления настройки сортировки.'
        );
    }

    /**
     * Обновляет сортировку элементов в ролях
     *
     * @param UpdateSortRequest $request
     * @return RedirectResponse
     */
    public function updateAdminSortRoles(UpdateSortRequest $request): RedirectResponse
    {
        return $this->updateSettingAndRedirect(
            $request,
            'AdminSortRoles',
            'site_settings.AdminSortRoles',
            'string',
            'admin_sort',
            'Сортировка по умолчанию успешно обновлена.',
            'Ошибка обновления настройки сортировки.'
        );
    }

    /**
     * Обновляет сортировку элементов в разрешениях
     *
     * @param UpdateSortRequest $request
     * @return RedirectResponse
     */
    public function updateAdminSortPermissions(UpdateSortRequest $request): RedirectResponse
    {
        return $this->updateSettingAndRedirect(
            $request,
            'AdminSortPermissions',
            'site_settings.AdminSortPermissions',
            'string',
            'admin_sort',
            'Сортировка по умолчанию успешно обновлена.',
            'Ошибка обновления настройки сортировки.'
        );
    }

    /**
     * Обновляет сортировку элементов в модулях
     *
     * @param UpdateSortRequest $request
     * @return RedirectResponse
     */
    public function updateAdminSortPlugins(UpdateSortRequest $request): RedirectResponse
    {
        return $this->updateSettingAndRedirect(
            $request,
            'AdminSortPlugins',
            'site_settings.AdminSortPlugins',
            'string',
            'admin_sort',
            'Сортировка по умолчанию успешно обновлена.',
            'Ошибка обновления настройки сортировки.'
        );
    }

    // --- Настройки панели виджетов ---
    /**
     * Получает настройку цвета панели виджетов в админке
     *
     * @return JsonResponse
     */
    public function getWidgetPanelSettings(): JsonResponse
    {
        // TODO: Авторизация (может быть в middleware группы /api/admin)
        try {
            $settings = Cache::remember('widget_panel_settings', self::SETTINGS_CACHE_TTL, function () {
                // Получаем значения или используем дефолты
                $color = Setting::where('option', 'widgetHexColor')->value('value') ?? '155E75';
                $opacity = Setting::where('option', 'widgetOpacity')->value('value') ?? 0.95;
                // Валидируем полученные значения на всякий случай перед кэшированием
                $color = preg_match('/^[0-9A-Fa-f]{6}$/i', $color) ? $color : '155E75';
                $opacity = is_numeric($opacity) && $opacity >= 0 && $opacity <= 1 ? (float)$opacity : 0.95;
                return ['color' => $color, 'opacity' => $opacity];
            });
            return response()->json($settings);
        } catch (Throwable $e) {
            Log::error('Ошибка получения настроек панели виджетов: ' . $e->getMessage());
            // Возвращаем дефолтные значения при ошибке
            return response()->json(['color' => '155E75', 'opacity' => 0.95], 500);
        }
    }

    /**
     * Обновляет настройку цвета панели виджетов в админке
     *
     * @param UpdateWidgetPanelRequest $request
     * @return JsonResponse
     */
    public function updateWidgetPanelSettings(UpdateWidgetPanelRequest $request): JsonResponse // Используем свой Request и JSON ответ
    {
        // Авторизация и валидация в UpdateWidgetPanelRequest
        $validated = $request->validated(); // 'color' (hex без #), 'opacity' (float 0-1)

        try {
            DB::beginTransaction();
            // Используем updateOrCreate, указываем все поля для консистентности
            Setting::updateOrCreate(
                ['option' => 'widgetHexColor'],
                [
                    'value' => $validated['color'],
                    'type' => 'string', // Тип значения
                    'constant' => 'WIDGET_HEX_COLOR', // Константа
                    'category' => 'widget_panel', // Категория
                    'activity' => true, // Активна по умолчанию
                ]
            );
            Setting::updateOrCreate(
                ['option' => 'widgetOpacity'],
                [
                    'value' => (string)$validated['opacity'], // Сохраняем как строку
                    'type' => 'float', // Указываем тип
                    'constant' => 'WIDGET_OPACITY',
                    'category' => 'widget_panel',
                    'activity' => true,
                ]
            );
            DB::commit();

            $this->clearSettingsCache('widget_panel_settings'); // Очищаем кэш виджета
            $this->clearSettingsCache(); // Очищаем общий кэш настроек
            Log::info('Настройки панели виджетов обновлены', $validated);
            // Возвращаем JSON с успехом
            return response()->json(['success' => true, 'message' => 'Настройки панели виджетов обновлены.']);

        } catch (Throwable $e) {
            DB::rollBack();
            Log::error('Ошибка обновления настроек панели виджетов: ' . $e->getMessage());
            // Возвращаем JSON с ошибкой
            return response()->json(['success' => false, 'message' => 'Ошибка сохранения настроек панели виджетов.'], 500);
        }
    }

    /**
     * Приватный метод для очистки кэша.
     *
     * @param string|null $specificKey
     * @return void
     */
    /**
     * Приватный метод для очистки кэша.
     * Исправлено для совместимости с RedisStore (используем цикл forget).
     */
    private function clearSettingsCache(string $specificKey = null): void
    {
        // TODO: Использовать ваши реальные базовые ключи кэша
        $keysToForget = ['site_settings', 'setting_locale', 'widget_panel_settings', 'sidebar_settings'];
        if ($specificKey) {
            $keysToForget[] = $specificKey;
        }
        // Добавляем ключи для всех настроек count и sort
        try {
            $options = Setting::where('option', 'like', 'AdminCount%')
                ->orWhere('option', 'like', 'AdminSort%')
                // Добавим ключи сайдбара/виджета, если они есть в БД
                ->orWhereIn('option', ['widgetHexColor', 'widgetOpacity', 'AdminSidebarLightColor', 'admin_sidebar_opacity']) // Пример
                ->pluck('option');
            foreach ($options as $option) {
                $keysToForget[] = 'setting_' . $option; // Ключ для конкретной настройки
            }
        } catch (Throwable $e) {
            // Логируем ошибку получения опций, но не прерываем очистку основных ключей
            Log::error("Ошибка получения опций для очистки кэша: " . $e->getMessage());
        }

        // --- ИСПРАВЛЕНИЕ: Используем цикл Cache::forget() ---
        $uniqueKeys = array_unique($keysToForget);
        foreach ($uniqueKeys as $key) {
            if (!empty($key)) { // Пропускаем пустые ключи на всякий случай
                Cache::forget($key);
            }
        }
        // --- КОНЕЦ ИСПРАВЛЕНИЯ ---

        Log::debug("Settings cache cleared.", ['keys_cleared' => $uniqueKeys]);
    }
}
