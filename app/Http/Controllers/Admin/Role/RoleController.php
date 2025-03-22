<?php

namespace App\Http\Controllers\Admin\Role;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Role\RoleRequest;
use App\Http\Resources\Admin\Permission\PermissionResource;
use App\Http\Resources\Admin\Role\RoleResource;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    public function index(): Response
    {
        // Прямое получение ролей и их подсчёт без кэширования
        $roles = Role::with('permissions')->get();
        $rolesCount = Role::count();

        return Inertia::render('Admin/Roles/Index', [
            'roles' => RoleResource::collection($roles),
            'rolesCount' => $rolesCount,
        ]);
    }

    public function create(): Response
    {
        // Прямое получение разрешений без кэширования
        $permissions = Permission::all();

        return Inertia::render('Admin/Roles/Create', [
            'permissions' => PermissionResource::collection($permissions),
        ]);
    }

    public function store(RoleRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $role = Role::create(['name' => $data['name']]);
        if ($request->filled('permissions')) {
            $role->syncPermissions($request->input('permissions'));
        }

        return redirect()->route('roles.index')
            ->with('success', 'Роль успешно создана.');
    }

    public function edit(string $id): Response
    {
        // Получаем роль и разрешения напрямую, без кэша
        $role = Role::with('permissions')->findOrFail($id);
        $permissions = Permission::all();

        return Inertia::render('Admin/Roles/Edit', [
            'role' => new RoleResource($role),
            'permissions' => PermissionResource::collection($permissions),
        ]);
    }

    public function update(RoleRequest $request, string $id): RedirectResponse
    {
        $role = Role::findById($id);
        $validatedData = $request->validated();
        $role->update(['name' => $validatedData['name']]);
        if ($request->filled('permissions')) {
            $role->syncPermissions($request->input('permissions'));
        }

        return redirect()->route('roles.index')
            ->with('success', 'Роль успешно обновлена.');
    }

    public function destroy(string $id): RedirectResponse
    {
        $role = Role::findById($id);
        $role->delete();

        Log::info('Role deleted:', $role->toArray());

        return back();
    }
}
