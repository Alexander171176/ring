<?php

namespace App\Http\Controllers\Admin\Permission;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Permission\PermissionRequest;
use App\Http\Resources\Admin\Permission\PermissionResource;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Permission\Models\Permission;

class PermissionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): Response
    {
        $permissions = Permission::all();
        $permissionsCount = Permission::count();

        return Inertia::render('Admin/Permissions/Index', [
            'permissions' => PermissionResource::collection($permissions),
            'permissionsCount' => $permissionsCount,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): Response
    {
        return Inertia::render('Admin/Permissions/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(PermissionRequest $request): RedirectResponse
    {
        $data = $request->validated();

        // Создание разрешения
        $permission = Permission::create(['name' => $data['name']]);

        // Log::info('Разрешение создано: ', $permission->toArray());

        return redirect()->route('permissions.index')->with('success', 'Разрешение успешно создано');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id): Response
    {
        $permission = Permission::findOrFail($id);

        return Inertia::render('Admin/Permissions/Edit', [
            'permission' => new PermissionResource($permission),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(PermissionRequest $request, string $id): RedirectResponse
    {
        $permission = Permission::findOrFail($id);

        $data = $request->validated();

        $permission->update(['name' => $data['name']]);

        // Log::info('Разрешение обновлено: ', $permission->toArray());

        return redirect()->route('permissions.index')->with('success', 'Разрешение успешно обновлено');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id): RedirectResponse
    {
        $permission = Permission::findOrFail($id);
        $permission->delete();

        // Log::info('Разрешение удалено: ', $permission->toArray());

        return back();
    }
}
