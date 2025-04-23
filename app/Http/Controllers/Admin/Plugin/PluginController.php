<?php

namespace App\Http\Controllers\Admin\Plugin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Plugin\PluginRequest; // Используем
// Реквесты для простых действий
use App\Http\Requests\Admin\UpdateActivityRequest;
use App\Http\Requests\Admin\UpdateSortRequest;
use App\Http\Resources\Admin\Plugin\PluginResource;
use App\Models\Admin\Plugin\Plugin;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request; // Для bulkDestroy
use Illuminate\Support\Facades\DB; // Для транзакций (опционально)
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse; // Используем псевдоним
use Throwable; // Для try/catch

class PluginController extends Controller
{
    /**
     * Отображение списка плагинов.
     */
    public function index(): InertiaResponse
    {
        // TODO: Проверка прав $this->authorize('viewAny', Plugin::class);
        $adminCountPlugins = config('site_settings.AdminCountPlugins', 15);
        $adminSortPlugins  = config('site_settings.AdminSortPlugins', 'idDesc');

        $sortField = 'id';
        $sortDirection = 'desc';
        if ($adminSortPlugins === 'sortAsc') { $sortField = 'sort'; $sortDirection = 'asc'; }
        elseif ($adminSortPlugins === 'nameAsc') { $sortField = 'name'; $sortDirection = 'asc'; }
        // Добавить другие варианты

        $plugins = Plugin::orderBy($sortField, $sortDirection)
            ->paginate($adminCountPlugins);

        $pluginCount = Plugin::count();

        return Inertia::render('Admin/Plugins/Index', [
            'plugins' => PluginResource::collection($plugins),
            'pluginsCount' => $pluginCount,
            'adminCountPlugins' => $adminCountPlugins,
            'adminSortPlugins' => $adminSortPlugins,
        ]);
    }

    /**
     * Показ формы создания плагина.
     */
    public function create(): InertiaResponse
    {
        // TODO: Проверка прав $this->authorize('create', Plugin::class);
        return Inertia::render('Admin/Plugins/Create');
    }

    /**
     * Сохранение нового плагина.
     */
    public function store(PluginRequest $request): RedirectResponse
    {
        // authorize() в PluginRequest
        $data = $request->validated();
        try {
            // Транзакция здесь не строго обязательна
            $plugin = Plugin::create($data);
            Log::info('Плагин успешно создан: ', ['id' => $plugin->id, 'name' => $plugin->name]);
            return redirect()->route('admin.plugins.index')->with('success', 'Плагин успешно создан.');
        } catch (Throwable $e) {
            Log::error("Ошибка при создании плагина: " . $e->getMessage());
            return back()->withInput()->withErrors(['general' => 'Произошла ошибка при создании плагина.']);
        }
    }

    /**
     * Показ информации о конкретном плагине (если нужно).
     * Рендерит стандартный шаблон Show.
     */
    public function show(Plugin $plugin): InertiaResponse // Используем RMB
    {
        // TODO: Проверка прав $this->authorize('view', $plugin);
        return Inertia::render('Admin/Plugins/Show', [
            'plugin' => new PluginResource($plugin),
            // Если нужно передать имя компонента для динамической загрузки на фронте:
            // 'pluginViewComponent' => $this->generatePluginViewName($plugin->name) // Пример
        ]);
    }

    /**
     * Показ формы редактирования плагина.
     */
    public function edit(Plugin $plugin): InertiaResponse // Используем RMB
    {
        // TODO: Проверка прав $this->authorize('update', $plugin);
        return Inertia::render('Admin/Plugins/Edit', [
            'plugin' => new PluginResource($plugin),
        ]);
    }

    /**
     * Обновление плагина.
     */
    public function update(PluginRequest $request, Plugin $plugin): RedirectResponse // Используем RMB
    {
        // authorize() в PluginRequest
        $data = $request->validated();
        try {
            $plugin->update($data);
            Log::info('Плагин обновлен: ', ['id' => $plugin->id, 'name' => $plugin->name]);
            return redirect()->route('admin.plugins.index')->with('success', 'Плагин успешно обновлен.');
        } catch (Throwable $e) {
            Log::error("Ошибка при обновлении плагина ID {$plugin->id}: " . $e->getMessage());
            return back()->withInput()->withErrors(['general' => 'Произошла ошибка при обновлении плагина.']);
        }
    }

    /**
     * Удаление плагина.
     */
    public function destroy(Plugin $plugin): RedirectResponse // Используем RMB
    {
        // TODO: Проверка прав $this->authorize('delete', $plugin);
        try {
            $plugin->delete();
            Log::info('Плагин удален: ID ' . $plugin->id);
            return redirect()->route('admin.plugins.index')->with('success', 'Плагин успешно удален.'); // Редирект на индекс
        } catch (Throwable $e) {
            Log::error("Ошибка при удалении плагина ID {$plugin->id}: " . $e->getMessage());
            return back()->withErrors(['general' => 'Произошла ошибка при удалении плагина.']);
        }
    }

    /**
     * Массовое удаление плагинов.
     */
    public function bulkDestroy(Request $request): JsonResponse // Оставляем Request
    {
        // TODO: Проверка прав $this->authorize('delete-bulk plugins');
        $validated = $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'required|integer|exists:plugins,id',
        ]);
        $pluginIds = $validated['ids'];
        try {
            Plugin::whereIn('id', $pluginIds)->delete();
            Log::info('Плагины удалены: ', $pluginIds);
            return response()->json(['success' => true, 'message' => 'Выбранные плагины удалены.', 'reload' => true]);
        } catch (Throwable $e) {
            Log::error("Ошибка при массовом удалении плагинов: " . $e->getMessage(), ['ids' => $pluginIds]);
            return response()->json(['success' => false, 'message' => 'Ошибка при удалении плагинов.'], 500);
        }
    }

    /**
     * Обновление активности плагина.
     */
    // Используем {plugin} в маршруте для RMB
    public function updateActivity(UpdateActivityRequest $request, Plugin $plugin): JsonResponse
    {
        // authorize() в UpdateActivityRequest
        $validated = $request->validated();
        $plugin->activity = $validated['activity'];
        $plugin->save();
        Log::info("Обновлено activity плагина ID {$plugin->id} на {$plugin->activity}");
        return response()->json(['success' => true, 'reload' => true]);
    }

    /**
     * Сортировка Плагинов.
     */
    // Используем {plugin} в маршруте для RMB
    public function updateSort(UpdateSortRequest $request, Plugin $plugin): JsonResponse
    {
        // authorize() в UpdateSortRequest
        $validated = $request->validated();
        $plugin->sort = $validated['sort'];
        $plugin->save();
        Log::info("Обновлено sort плагина ID {$plugin->id} на {$plugin->sort}");
        return response()->json(['success' => true]);
    }

    /**
     * Вспомогательная функция для генерации имени компонента (пример)
     * Нужна, если вы используете динамический рендеринг в методе show()
     */
    /*
    private function generatePluginViewName(string $pluginName): string
    {
        // Преобразует 'my_plugin' или 'my-plugin' в 'MyPlugin'
        return str_replace(['_', '-'], '', ucwords($pluginName, '_-'));
    }
    */

    // Методы clone для плагинов обычно не нужны.
    // Методы getPluginData, getPluginSettings, updatePluginSettings вынесены в трейты или ApiPluginController? Оставляем их там.
}
