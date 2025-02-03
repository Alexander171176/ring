<?php

namespace App\Http\Controllers\Admin\User;

use App\Actions\Fortify\PasswordValidationRules;
use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\Permission\PermissionResource;
use App\Http\Resources\Admin\Role\RoleResource;
use App\Http\Resources\Admin\User\UserResource;
use App\Models\User;
use App\Traits\CacheTimeTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules;
use Inertia\Inertia;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    use CacheTimeTrait;
    use PasswordValidationRules;

    /**
     * Display a listing of the resource.
     */
    public function index(): \Inertia\Response
    {
        $cacheTime = $this->getCacheTime();

        $users = Cache::store('redis')->remember('users.all', $cacheTime, function () {
            return User::with(['roles', 'permissions'])->get();
        });

        $usersCount = Cache::store('redis')->remember('users.count', $cacheTime, function () {
            return User::count();
        });

        return Inertia::render('Admin/Users/Index', [
            'users' => UserResource::collection($users),
            'usersCount' => $usersCount,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): \Inertia\Response
    {
        $cacheTime = $this->getCacheTime();

        $roles = Cache::store('redis')->remember('roles.all', $cacheTime, function () {
            return Role::all();
        });

        $permissions = Cache::store('redis')->remember('permissions.all', $cacheTime, function () {
            return Permission::all();
        });

        return Inertia::render('Admin/Users/Create', [
            'roles' => RoleResource::collection($roles),
            'permissions' => PermissionResource::collection($permissions)
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): \Illuminate\Http\RedirectResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique(User::class)],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'roles' => ['sometimes', 'array'],
            'permissions' => ['sometimes', 'array']
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);

        if (isset($data['roles'])) {
            $user->syncRoles($data['roles']);
        }

        if (isset($data['permissions'])) {
            $user->syncPermissions($data['permissions']);
        }

        $user->load(['roles', 'permissions']);

        Log::info('Пользователь создан:', $user->toArray());

        $this->clearCache(['users', 'roles', 'permissions']);

        return redirect()->route('users.index')->with('success', 'Пользователь успешно создан');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $user): \Inertia\Response
    {
        $user->load(['roles', 'permissions']);

        $roles = Role::all();
        $permissions = Permission::all();

        return Inertia::render('Admin/Users/Edit', [
            'user' => new UserResource($user),
            'roles' => RoleResource::collection($roles),
            'permissions' => PermissionResource::collection($permissions)
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user): \Illuminate\Http\RedirectResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'roles' => ['sometimes', 'array'],
            'permissions' => ['sometimes', 'array']
        ]);

        $user->update([
            'name' => $data['name'],
            'email' => $data['email'],
        ]);

        if (isset($data['roles'])) {
            $user->syncRoles($data['roles']);
        }

        if (isset($data['permissions'])) {
            $user->syncPermissions($data['permissions']);
        }

        $user->load(['roles', 'permissions']);

        Log::info('Пользователь обновлён:', $user->toArray());

        $this->clearCache(['users', 'roles', 'permissions']);

        return redirect()->route('users.index')->with('success', 'Пользователь успешно обновлён');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user): \Illuminate\Http\RedirectResponse
    {
        $user->delete();

        Log::info('Пользователь удалён:', $user->toArray());

        $this->clearCache(['users', 'roles', 'permissions']);

        return redirect()->route('users.index')->with('success', 'Пользователь успешно удалён');
    }

}
