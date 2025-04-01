<?php

namespace App\Http\Controllers\Admin\Plugin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Plugin\PluginRequest;
use App\Http\Resources\Admin\Plugin\PluginResource;
use App\Models\Admin\Plugin\Plugin;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response;

class PluginController extends Controller
{
    public function index(): Response
    {
        $plugins = Plugin::all();
        $pluginCount = Plugin::count();

        // Получаем значение параметра из конфигурации (оно загружается через AppServiceProvider)
        $adminCountPlugins = config('site_settings.AdminCountPlugins', 10);

        return Inertia::render('Admin/Plugins/Index', [
            'plugins' => PluginResource::collection($plugins),
            'pluginsCount' => $pluginCount,
            'adminCountPlugins' => (int)$adminCountPlugins,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/Plugins/Create');
    }

    public function store(PluginRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $plugin = Plugin::create($data);

        // Log::info('Созданный плагин: ', $plugin->toArray());

        return redirect()->route('plugins.index')->with('success', 'Плагин успешно создан.');
    }

    public function show(string $id): Response
    {
        $plugin = Plugin::findOrFail($id);
        $pluginName = ucfirst($plugin->name);

        return Inertia::render("Plugins/{$pluginName}/Index", [
            'plugin' => new PluginResource($plugin),
            'pluginName' => $pluginName,
        ]);
    }

    public function edit(string $id): Response
    {
        $plugin = Plugin::findOrFail($id);

        return Inertia::render('Admin/Plugins/Edit', [
            'plugin' => new PluginResource($plugin),
        ]);
    }

    public function update(PluginRequest $request, string $id): RedirectResponse
    {
        $plugin = Plugin::findOrFail($id);
        $data = $request->validated();
        $plugin->update($data);

        // Log::info('Обновлен плагин: ', $plugin->toArray());

        return redirect()->route('plugins.index')->with('success', 'Плагин успешно обновлен.');
    }

    public function destroy(string $id): RedirectResponse
    {
        $plugin = Plugin::findOrFail($id);
        $plugin->delete();

        // Log::info('Plugin deleted: ', $plugin->toArray());

        return back();
    }

    public function updateActivity(Request $request, $id): JsonResponse
    {
        $validated = $request->validate(['activity' => 'boolean']);
        $plugin = Plugin::findOrFail($id);
        $plugin->activity = $validated['activity'];
        $plugin->save();

        return response()->json(['success' => true, 'reload' => true]);
    }

    public function updateSort(Request $request, $id): JsonResponse
    {
        $validated = $request->validate(['sort' => 'required|integer']);
        $plugin = Plugin::findOrFail($id);
        $plugin->sort = $validated['sort'];
        $plugin->save();

        return response()->json(['success' => true]);
    }
}
