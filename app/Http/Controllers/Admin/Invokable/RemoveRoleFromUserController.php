<?php

namespace App\Http\Controllers\Admin\Invokable;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;

class RemoveRoleFromUserController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(User $user, Role $role): RedirectResponse
    {
        $user->removeRole($role);
        return back()->with('success', 'Роль успешно удалена у пользователя');
    }
}
