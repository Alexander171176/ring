<?php

namespace App\Http\Controllers\Admin\Invokable;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RemovePermissionFromRoleController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Role $role, Permission $permission): \Illuminate\Http\RedirectResponse
    {
        $role->revokePermissionTo($permission);
        return back()->with('success', 'Разрешение успешно удалено из роли');
    }
}
