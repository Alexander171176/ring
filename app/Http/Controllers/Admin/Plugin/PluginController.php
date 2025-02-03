<?php

namespace App\Http\Controllers\Admin\Plugin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Plugin\PluginRequest;
use App\Http\Resources\Admin\Plugin\PluginResource;
use App\Models\Admin\Plugin\Plugin;
use App\Traits\CacheTimeTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class PluginController extends Controller
{
    use CacheTimeTrait;

    public function index(): \Inertia\Response
    {
        $cacheTime = $this->getCacheTime();

        $plugins = Cache::store('redis')->remember('plugins.all', $cacheTime, function () {
            return Plugin::all();
        });

        $pluginCount = Cache::store('redis')->remember('plugins.count', $cacheTime, function () {
            return Plugin::count();
        });

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

        $this->clearCache(['plugins.all', 'plugins.count']);

        return redirect()->route('plugins.index')->with('success', 'Плагин успешно создан.');
    }

    public function show(string $id): \Inertia\Response
    {
        $cacheTime = $this->getCacheTime();
        $plugin = Cache::store('redis')->remember("plugin.{$id}", $cacheTime, function () use ($id) {
            return Plugin::findOrFail($id);
        });

        $pluginName = ucfirst($plugin->name);
        return Inertia::render("Plugins/{$pluginName}/Index", [
            'plugin' => new PluginResource($plugin),
            'pluginName' => $pluginName,
        ]);
    }

    public function edit(string $id): \Inertia\Response
    {
        $cacheTime = $this->getCacheTime();
        $plugin = Cache::store('redis')->remember("plugin.$id", $cacheTime, function () use ($id) {
            return Plugin::findOrFail($id);
        });

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

        $this->clearCache(['plugins.all', 'plugins.count', "plugin.$id"]);

        return redirect()->route('plugins.index')->with('success', 'Плагин успешно обновлен.');
    }

    public function destroy(string $id): \Illuminate\Http\RedirectResponse
    {
        $plugin = Plugin::findOrFail($id);
        $plugin->delete();

        Log::info('Plugin deleted: ', $plugin->toArray());

        $this->clearCache(['plugins.all', 'plugins.count', "plugin.$id"]);

        return back();
    }

    public function updateActivity(Request $request, $id): \Illuminate\Http\JsonResponse
    {
        $validated = $request->validate(['activity' => 'boolean']);
        $plugin = Plugin::findOrFail($id);
        $plugin->activity = $validated['activity'];
        $plugin->save();

        $this->clearCache(['plugins.all', 'plugins.count', "plugin.$id"]);

        return response()->json(['success' => true, 'reload' => true]);
    }

    public function updateSort(Request $request, $id): \Illuminate\Http\JsonResponse
    {
        $validated = $request->validate(['sort' => 'required|integer']);
        $plugin = Plugin::findOrFail($id);
        $plugin->sort = $validated['sort'];
        $plugin->save();

        $this->clearCache(['plugins.all', 'plugins.count', "plugin.$id"]);

        return response()->json(['success' => true]);
    }
}
