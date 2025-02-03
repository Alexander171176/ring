<?php

namespace App\Http\Controllers\Admin\Role;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Role\RoleRequest;
use App\Http\Resources\Admin\Permission\PermissionResource;
use App\Http\Resources\Admin\Role\RoleResource;
use App\Traits\CacheTimeTrait;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    use CacheTimeTrait;

    public function index(): \Inertia\Response
    {
        $cacheTime = $this->getCacheTime();

        $roles = Cache::store('redis')->remember('roles.all', $cacheTime, function () {
            return Role::with('permissions')->get();
        });

        $rolesCount = Cache::store('redis')->remember('roles.count', $cacheTime, function () {
            return Role::count();
        });

        return Inertia::render('Admin/Roles/Index', [
            'roles' => RoleResource::collection($roles),
            'rolesCount' => $rolesCount,
        ]);
    }

    public function create(): \Inertia\Response
    {
        $cacheTime = $this->getCacheTime();

        $permissions = Cache::store('redis')->remember('permissions.all', $cacheTime, function () {
            return Permission::all();
        });

        return Inertia::render('Admin/Roles/Create', [
            'permissions' => PermissionResource::collection($permissions),
        ]);
    }

    public function store(RoleRequest $request): \Illuminate\Http\RedirectResponse
    {
        $data = $request->validated();
        $role = Role::create(['name' => $data['name']]);
        if ($request->filled('permissions')) {
            $role->syncPermissions($request->input('permissions'));
        }

        $this->clearCache(['roles.all', 'roles.count', 'permissions.all']);

        return redirect()->route('roles.index')
            ->with('success', 'Роль успешно создана.');
    }

    public function edit(string $id): \Inertia\Response
    {
        $role = Role::with('permissions')->find($id); // Получаем роль напрямую, без кэша
        $permissions = Permission::all(); // Получаем разрешения напрямую, без кэша

        return Inertia::render('Admin/Roles/Edit', [
            'role' => new RoleResource($role),
            'permissions' => PermissionResource::collection($permissions),
        ]);
    }

    public function update(RoleRequest $request, string $id): \Illuminate\Http\RedirectResponse
    {
        $role = Role::findById($id);
        $role->update(['name' => $request->validated('name')]);
        if ($request->filled('permissions')) {
            $role->syncPermissions($request->input('permissions'));
        }

        $this->clearCache(['roles.all', 'roles.count', 'permissions.all', "role.$id"]);

        return redirect()->route('roles.index')
            ->with('success', 'Роль успешно обновлена.');
    }

    public function destroy(string $id): \Illuminate\Http\RedirectResponse
    {
        $role = Role::findById($id);
        $role->delete();

        Log::info('Role deleted:', $role->toArray());

        $this->clearCache(['roles.all', 'roles.count', 'permissions.all', "role.$id"]);

        return back();
    }

}
