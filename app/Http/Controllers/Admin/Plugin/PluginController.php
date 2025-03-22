<?php

namespace App\Http\Controllers\Admin\Plugin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Plugin\PluginRequest;
use App\Http\Resources\Admin\Plugin\PluginResource;
use App\Models\Admin\Plugin\Plugin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class PluginController extends Controller
{
    public function index(): \Inertia\Response
    {
        $plugins = Plugin::all();
        $pluginCount = Plugin::count();

        return Inertia::render('Admin/Plugins/Index', [
            'plugins' => PluginResource::collection($plugins),
            'pluginsCount' => $pluginCount,
        ]);
    }

    public function create(): \Inertia\Response
    {
        return Inertia::render('Admin/Plugins/Create');
    }

    public function store(PluginRequest $request): \Illuminate\Http\RedirectResponse
    {
        $data = $request->validated();
        $plugin = Plugin::create($data);

        Log::info('Созданный плагин: ', $plugin->toArray());

        return redirect()->route('plugins.index')->with('success', 'Плагин успешно создан.');
    }

    public function show(string $id): \Inertia\Response
    {
        $plugin = Plugin::findOrFail($id);
        $pluginName = ucfirst($plugin->name);

        return Inertia::render("Plugins/{$pluginName}/Index", [
            'plugin' => new PluginResource($plugin),
            'pluginName' => $pluginName,
        ]);
    }

    public function edit(string $id): \Inertia\Response
    {
        $plugin = Plugin::findOrFail($id);

        return Inertia::render('Admin/Plugins/Edit', [
            'plugin' => new PluginResource($plugin),
        ]);
    }

    public function update(PluginRequest $request, string $id): \Illuminate\Http\RedirectResponse
    {
        $plugin = Plugin::findOrFail($id);
        $data = $request->validated();
        $plugin->update($data);

        Log::info('Обновлен плагин: ', $plugin->toArray());

        return redirect()->route('plugins.index')->with('success', 'Плагин успешно обновлен.');
    }

    public function destroy(string $id): \Illuminate\Http\RedirectResponse
    {
        $plugin = Plugin::findOrFail($id);
        $plugin->delete();

        Log::info('Plugin deleted: ', $plugin->toArray());

        return back();
    }

    public function updateActivity(Request $request, $id): \Illuminate\Http\JsonResponse
    {
        $validated = $request->validate(['activity' => 'boolean']);
        $plugin = Plugin::findOrFail($id);
        $plugin->activity = $validated['activity'];
        $plugin->save();

        return response()->json(['success' => true, 'reload' => true]);
    }

    public function updateSort(Request $request, $id): \Illuminate\Http\JsonResponse
    {
        $validated = $request->validate(['sort' => 'required|integer']);
        $plugin = Plugin::findOrFail($id);
        $plugin->sort = $validated['sort'];
        $plugin->save();

        return response()->json(['success' => true]);
    }
}
