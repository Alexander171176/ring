<?php

namespace App\Http\Controllers\Admin\Parameter;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Setting\SettingRequest; // Используем общий реквест для настроек
// Реквесты для простых действий
use App\Http\Requests\Admin\UpdateActivityRequest;
use App\Http\Requests\Admin\UpdateSortRequest; // Если параметры нужно сортировать
use App\Http\Resources\Admin\Setting\SettingResource; // Используем общий ресурс
use App\Models\Admin\Setting\Setting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request; // Для bulkDestroy
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;
use Throwable;
use Illuminate\Database\Eloquent\Builder; // Для типизации $query

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
     * Отображение списка параметров системы.
     */
    public function index(): Response
    {
        // TODO: Проверка прав $this->authorize('view parameters');
        // Используем тот же конфиг, что и для Settings? Или нужен отдельный?
        $adminCountParameters = config('site_settings.AdminCountParameters', 15); // Используем свой ключ или общий
        $adminSortParameters  = config('site_settings.AdminSortParameters', 'idDesc'); // Используем свой ключ или общий

        $sortField = 'id';
        $sortDirection = 'desc';
        if ($adminSortParameters === 'categoryAsc') { $sortField = 'category'; $sortDirection = 'asc'; }
        elseif ($adminSortParameters === 'optionAsc') { $sortField = 'option'; $sortDirection = 'asc'; }

        // Используем базовый запрос с фильтром
        $parameters = $this->getParameterQuery()
            ->orderBy($sortField, $sortDirection)
            ->paginate($adminCountParameters);

        // Считаем только параметры
        $parametersCount = $this->getParameterQuery()->count();

        return Inertia::render('Admin/Parameters/Index', [
            // Используем SettingResource, но передаем как 'parameters'
            'parameters' => SettingResource::collection($parameters),
            'parametersCount' => $parametersCount, // Количество только параметров
            'adminCountParameters' => $adminCountParameters, // Передаем свои настройки
            'adminSortParameters' => $adminSortParameters,
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
     * Массовое удаление параметров системы.
     */
    public function bulkDestroy(Request $request): JsonResponse
    {
        // TODO: Проверка прав $this->authorize('delete-bulk parameters');
        $validated = $request->validate([
            'ids' => 'required|array',
            // Проверяем, что ID существуют и относятся к нужной категории/типу
            'ids.*' => ['required', 'integer', Rule::exists('settings', 'id')->where(function ($query) {
                // TODO: Адаптировать условие where
                $query->where('type', 'parameter');
                // $query->where('category', self::PARAMETER_CATEGORY);
            })],
        ]);
        $parameterIds = $validated['ids'];
        try {
            // Удаляем только валидированные параметры
            Setting::whereIn('id', $parameterIds)->delete();
            Log::info('Параметры системы удалены: ', $parameterIds);
            return response()->json(['success' => true, 'message' => 'Выбранные параметры удалены.', 'reload' => true]);
        } catch (Throwable $e) {
            Log::error("Ошибка при массовом удалении параметров: " . $e->getMessage(), ['ids' => $parameterIds]);
            return response()->json(['success' => false, 'message' => 'Ошибка при удалении параметров.'], 500);
        }
    }

    /**
     * Обновление активности параметра системы.
     */
    // Используем {parameter} в маршруте для RMB
    public function updateActivity(UpdateActivityRequest $request, Setting $parameter): JsonResponse
    {
        // TODO: Проверка прав $this->authorize('update', $parameter);
        // Дополнительная проверка
        // TODO: Адаптировать проверку
        if ($parameter->type !== 'parameter') {
            return response()->json(['success' => false, 'message' => 'Действие неприменимо.'], 403);
        }

        $validated = $request->validated();
        $parameter->activity = $validated['activity'];
        $parameter->save();
        Log::info("Обновлено activity параметра ID {$parameter->id} на {$parameter->activity}");
        return response()->json(['success' => true, 'reload' => true]);
    }

    // Метод updateSort для параметров может быть не нужен? Если нужен - добавить по аналогии.
    // Метод clone для параметров обычно не нужен.
}
