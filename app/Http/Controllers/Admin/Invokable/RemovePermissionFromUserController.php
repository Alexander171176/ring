<?php

namespace App\Http\Controllers\Admin\Invokable;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;

class RemovePermissionFromUserController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(User $user, Permission $permission): RedirectResponse
    {
        $user->revokePermissionTo($permission);
        return back()->with('success', 'Разрешение успешно удалено у пользователя');
    }
}
