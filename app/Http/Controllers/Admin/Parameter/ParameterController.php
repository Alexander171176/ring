<?php

namespace App\Http\Controllers\Admin\Parameter;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Setting\SettingRequest; // Используем общий реквест для настроек
// Реквесты для простых действий
use App\Http\Requests\Admin\UpdateActivityRequest;
use App\Http\Requests\Admin\UpdateSortEntityRequest;
use App\Http\Requests\Admin\UpdateSortRequest; // Если параметры нужно сортировать
use App\Http\Resources\Admin\Setting\SettingResource; // Используем общий ресурс
use App\Models\Admin\Setting\Setting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request; // Для bulkDestroy
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;
use Throwable;
use Illuminate\Database\Eloquent\Builder; // Для типизации $query

/**
 * Контроллер для управления Параметрами системы в административной панели.
 *
 * Предоставляет CRUD операции, а также дополнительные действия:
 * - Обновление активности и сортировки (одиночное и массовое)
 *
 * @version 1.1 (Улучшен с RMB, транзакциями, Form Requests)
 * @author Александр Косолапов <kosolapov1976@gmail.com>
 * @see \App\Models\Admin\Setting\Setting Модель Статьи
 * @see \App\Http\Requests\Admin\Setting\SettingRequest Запрос для создания/обновления
 */
class ParameterController extends Controller
{
    // Определяем категорию для этого контроллера
    private const PARAMETER_CATEGORY = 'system'; // TODO: Замените на ваше значение категории

    /**
     * Применяет базовый скоуп для выборки только параметров системы.
     */
    private function getParameterQuery(): Builder
    {
        // TODO: Адаптируйте условие where под ваш способ идентификации параметров
        // return Setting::where('category', self::PARAMETER_CATEGORY);
        // Или по типу:
        return Setting::where('type', 'parameter');
        // Или по списку опций:
        // return Setting::whereIn('option', ['option1', 'option2', ...]);
    }

    /**
     * Отображение списка всех Параметров.
     * Загружает пагинированный список с сортировкой по настройкам.
     * Передает данные для отображения и настройки пагинации/сортировки.
     * Пагинация и сортировка выполняются на фронтенде.
     *
     * @return Response
     */
    public function index(): Response
    {
        // TODO: Проверка прав $this->authorize('show-parameters');

        // Используем тот же конфиг, что и для Settings? Или нужен отдельный?
        $adminCountSettings = config('site_settings.AdminCountSettings', 15); // Используем свой ключ или общий
        $adminSortSettings  = config('site_settings.AdminSortSettings', 'idDesc'); // Используем свой ключ или общий

        try {
            $settings = Setting::all();
            $settingsCount = Setting::count();
        } catch (Throwable $e) {
            Log::error("Ошибка загрузки параметров для Index: " . $e->getMessage());
            $settings = collect(); // Пустая коллекция в случае ошибки
            $settingsCount = 0;
            session()->flash('error', 'Не удалось загрузить список параметров.');
        }

        return Inertia::render('Admin/Parameters/Index', [
            // Используем SettingResource, но передаем как 'parameters'
            'settings' => SettingResource::collection($settings),
            'settingsCount' => $settingsCount,
            'adminCountSettings' => (int)$adminCountSettings,
            'adminSortSettings' => $adminSortSettings,
        ]);
    }

    /**
     * Показ формы создания параметра системы.
     */
    public function create(): Response
    {
        // TODO: Проверка прав $this->authorize('create', Setting::class); // Или кастомное право
        return Inertia::render('Admin/Parameters/Create', [
            // Передаем дефолтное значение категории, если нужно
            // 'defaultCategory' => self::PARAMETER_CATEGORY,
        ]);
    }

    /**
     * Создание параметра системы.
     */
    public function store(SettingRequest $request): RedirectResponse
    {
        // TODO: Проверка прав $this->authorize('create', Setting::class);
        $data = $request->validated();

        // Принудительно устанавливаем категорию (или тип), если она не передается из формы
        // TODO: Адаптировать под ваш способ идентификации
        // $data['category'] = self::PARAMETER_CATEGORY;
        $data['type'] = 'parameter'; // Пример

        try {
            Setting::create($data);
            Log::info('Параметр системы успешно создан: ', ['option' => $data['option']]);
            // Редирект на индекс параметров
            return redirect()->route('admin.parameters.index')->with('success', 'Параметр системы успешно создан.');
        } catch (Throwable $e) {
            Log::error("Ошибка при создании параметра: " . $e->getMessage());
            return back()->withInput()->withErrors(['general' => 'Произошла ошибка при создании параметра.']);
        }
    }

    /**
     * Показ формы редактирования параметра системы.
     */
    // Используем RMB {parameter}, но тип будет Setting $setting
    public function edit(Setting $parameter): Response // Меняем имя переменной на $parameter
    {
        // TODO: Проверка прав $this->authorize('update', $parameter);
        // Дополнительная проверка, что это действительно параметр
        // TODO: Адаптировать проверку
        if ($parameter->type !== 'parameter') {
            abort(404, 'Настройка не является параметром системы.');
        }

        return Inertia::render('Admin/Parameters/Edit', [
            // Передаем как 'parameter', используем SettingResource
            'parameter' => new SettingResource($parameter),
        ]);
    }

    /**
     * Обновление параметра системы.
     */
    // Используем RMB {parameter} и SettingRequest
    public function update(SettingRequest $request, Setting $parameter): RedirectResponse
    {
        // TODO: Проверка прав $this->authorize('update', $parameter);
        // Дополнительная проверка
        // TODO: Адаптировать проверку
        if ($parameter->type !== 'parameter') {
            abort(403, 'Вы не можете редактировать эту настройку как параметр системы.');
        }

        $data = $request->validated();
        // Запрещаем менять категорию/тип через эту форму (если нужно)
        // TODO: Адаптировать
        unset($data['type'], $data['category'], $data['option'], $data['constant']); // Запрещаем менять ключевые поля

        try {
            $parameter->update($data);
            Log::info('Параметр системы обновлен: ', ['id' => $parameter->id, 'option' => $parameter->option]);
            return redirect()->route('admin.parameters.index')->with('success', 'Параметр системы успешно обновлен.');
        } catch (Throwable $e) {
            Log::error("Ошибка при обновлении параметра ID {$parameter->id}: " . $e->getMessage());
            return back()->withInput()->withErrors(['general' => 'Произошла ошибка при обновлении параметра.']);
        }
    }

    /**
     * Удаление параметра системы.
     */
    // Используем RMB {parameter}
    public function destroy(Setting $parameter): RedirectResponse
    {
        // TODO: Проверка прав $this->authorize('delete', $parameter);
        // Дополнительная проверка
        // TODO: Адаптировать проверку
        if ($parameter->type !== 'parameter') {
            abort(403, 'Вы не можете удалить эту настройку как параметр системы.');
        }

        try {
            $parameter->delete();
            Log::info('Параметр системы удален: ID ' . $parameter->id);
            return redirect()->route('admin.parameters.index')->with('success', 'Параметр системы успешно удален.');
        } catch (Throwable $e) {
            Log::error("Ошибка при удалении параметра ID {$parameter->id}: " . $e->getMessage());
            return back()->withErrors(['general' => 'Произошла ошибка при удалении параметра.']);
        }
    }

    /**
     * Обновление статуса активности параметра.
     *
     * @param UpdateActivityRequest $request
     * @param Setting $setting
     * @return RedirectResponse
     */
    public function updateActivity(UpdateActivityRequest $request, Setting $setting): RedirectResponse
    {
        $validated = $request->validated();

        if (in_array($setting->category, ['system', 'admin', 'public'], true)) {
            Log::info("Попытка изменения активности параметра ID {$setting->id} с категорией '{$setting->category}'.");

            return back()->with('warning', __('admin/parameters.activity_update_forbidden', [
                'category' => $setting->category,
            ]));
        }

        try {
            $setting->activity = $validated['activity'];
            $setting->save();

            $actionText = $setting->activity ? 'активирован' : 'деактивирован';
            Log::info("Параметр ID {$setting->id} успешно {$actionText}");

            return back()->with('success', __('admin/parameters.update_activity_success', [
                'option' => $setting->option,
                'action' => $actionText,
            ]));
        } catch (Throwable $e) {
            Log::error("Ошибка обновления активности параметра ID {$setting->id}: " . $e->getMessage());

            return back()->withErrors([
                'general' => __('admin/parameters.update_activity_error'),
            ]);
        }
    }

    /**
     * Обновление статуса активности массово
     *
     * @param Request $request
     * @return JsonResponse Json ответ
     */
    public function bulkUpdateActivity(Request $request): JsonResponse
    {
        $data = $request->validate([
            'ids'      => 'required|array',
            'ids.*'    => 'required|integer|exists:settings,id',
            'activity' => 'required|boolean',
        ]);

        Setting::whereIn('id', $data['ids'])->update(['activity' => $data['activity']]);

        return response()->json(['success' => true]);
    }

    /**
     * Обновление значения сортировки для одного параметра.
     * Использует Route Model Binding и UpdateSortRequest.
     * *
     * @param UpdateSortEntityRequest $request Валидированный запрос с полем 'sort'.
     * @param Setting $parameter Модель параметра для обновления.
     * @return RedirectResponse Редирект назад с сообщением..
     */
    public function updateSort(UpdateSortEntityRequest $request, Setting $parameter): RedirectResponse
    {
        // authorize() в UpdateSortEntityRequest
        $validated = $request->validated();
        try {
            $parameter->sort = $validated['sort'];
            $parameter->save();
            Log::info("Обновлено sort тега ID {$parameter->id} на {$parameter->sort}");
            return back();

        } catch (Throwable $e) {
            Log::error("Ошибка обновления сортировки тега ID {$parameter->id}: " . $e->getMessage());
            return back()->withErrors(['sort' => 'Не удалось обновить сортировку.']);
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

        $uniqueKeys = array_unique($keysToForget);
        foreach ($uniqueKeys as $key) {
            if (!empty($key)) { // Пропускаем пустые ключи на всякий случай
                Cache::forget($key);
            }
        }

        Log::debug("Кэш настроек очищен.", ['keys_cleared' => $uniqueKeys]);
    }
}
