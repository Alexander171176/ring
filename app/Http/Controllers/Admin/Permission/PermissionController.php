<?php

namespace App\Http\Controllers\Admin\Permission;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Permission\PermissionRequest;
use App\Http\Resources\Admin\Permission\PermissionResource;
use App\Traits\CacheTimeTrait;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Spatie\Permission\Models\Permission;

class PermissionController extends Controller
{
    use CacheTimeTrait;

    /**
     * Display a listing of the resource.
     */
    public function index(): \Inertia\Response
    {
        $cacheTime = $this->getCacheTime();

        $permissions = Cache::store('redis')->remember('permissions.all', $cacheTime, function () {
            return Permission::all();
        });

        $permissionsCount = Cache::store('redis')->remember('permissions.count', $cacheTime, function () {
            return Permission::count();
        });

        return Inertia::render('Admin/Permissions/Index', [
            'permissions' => PermissionResource::collection($permissions),
            'permissionsCount' => $permissionsCount,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): \Inertia\Response
    {
        return Inertia::render('Admin/Permissions/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(PermissionRequest $request): \Illuminate\Http\RedirectResponse
    {
        $data = $request->validated();

        // Создание разрешения
        $permission = Permission::create(['name' => $data['name']]);

        Log::info('Разрешение создано: ', $permission->toArray());

        $this->clearCache(['permissions.all', 'permissions.count']);

        return redirect()->route('permissions.index')->with('success', 'Разрешение успешно создано');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id): \Inertia\Response
    {
        $cacheTime = $this->getCacheTime();

        $permission = Cache::store('redis')->remember("permission.$id", $cacheTime, function () use ($id) {
            return Permission::findOrFail($id);
        });

        return Inertia::render('Admin/Permissions/Edit', [
            'permission' => new PermissionResource($permission),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(PermissionRequest $request, string $id): \Illuminate\Http\RedirectResponse
    {
        $permission = Permission::findOrFail($id);

        $data = $request->validated();

        $permission->update(['name' => $data['name']]);

        Log::info('Разрешение обновлено: ', $permission->toArray());

        $this->clearCache(['permissions.all', 'permissions.count', "permission.$id"]);

        return redirect()->route('permissions.index')->with('success', 'Разрешение успешно обновлено');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id): \Illuminate\Http\RedirectResponse
    {
        $permission = Permission::findOrFail($id);
        $permission->delete();

        Log::info('Разрешение удалено: ', $permission->toArray());

        $this->clearCache(['permissions.all', 'permissions.count']);

        return back();
    }
}
