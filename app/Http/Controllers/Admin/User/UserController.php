<?php

namespace App\Http\Controllers\Admin\User;

use App\Actions\Fortify\PasswordValidationRules; // Для правил пароля
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\User\StoreUserRequest; // <--- Новый Request для store
use App\Http\Requests\Admin\User\UpdateUserRequest; // <--- Новый Request для update
// Ресурсы
use App\Http\Resources\Admin\User\UserResource;
use App\Http\Resources\Admin\Role\RoleResource;          // Для списка ролей
use App\Http\Resources\Admin\Permission\PermissionResource; // Для списка разрешений
// Модели
use App\Models\User;
use Spatie\Permission\Models\Role;          // Используем модели Spatie
use Spatie\Permission\Models\Permission;    // Используем модели Spatie
// Другое
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request; // Для bulkDestroy
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse; // Используем псевдоним
use Throwable;

class UserController extends Controller
{
    // Трейт для правил валидации пароля из Fortify/Jetstream
    use PasswordValidationRules;

    /**
     * Отображение списка пользователей.
     */
    public function index(): InertiaResponse
    {
        // TODO: Проверка прав $this->authorize('viewAny', User::class);
        $adminCountUsers = config('site_settings.AdminCountUsers', 15);
        $adminSortUsers  = config('site_settings.AdminSortUsers', 'idDesc');

        // Прямой запрос без кэширования
        $users = User::with(['roles', 'permissions'])->get();
        $usersCount = User::count();

        return Inertia::render('Admin/Users/Index', [
            'users' => UserResource::collection($users), // Используем полный ресурс
            'usersCount' => $usersCount,
            'adminCountUsers' => (int)$adminCountUsers,
            'adminSortUsers' => $adminSortUsers,
        ]);
    }

    /**
     * Показ формы создания пользователя.
     */
    public function create(): InertiaResponse
    {
        // TODO: Проверка прав $this->authorize('create', User::class);
        // Загружаем только ID и имя для списков
        $roles = Role::select('id', 'name')->orderBy('name')->get();
        $permissions = Permission::select('id', 'name')->orderBy('name')->get();

        return Inertia::render('Admin/Users/Create', [
            // Передаем коллекции для селектов (Resource не нужен)
            'roles' => $roles,
            'permissions' => $permissions,
        ]);
    }

    /**
     * Создание нового пользователя.
     */
    public function store(StoreUserRequest $request): RedirectResponse // <--- Используем StoreUserRequest
    {
        // Авторизация и валидация в StoreUserRequest
        $data = $request->validated();

        try {
            DB::beginTransaction();

            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                // Можно добавить другие поля из $fillable модели User, если они есть в $data
                // 'email_verified_at' => now(), // Пример: сразу верифицировать
            ]);

            // Назначаем роли и разрешения (реквест должен валидировать их существование)
            // Spatie ожидает массив имен или ID, или объектов Role/Permission
            if (!empty($data['roles'])) {
                // Если фронтенд шлет массив объектов {id: ..., name: ...}, извлекаем name или id
                $roleNamesOrIds = collect($data['roles'])->pluck('name')->toArray(); // Предполагаем, что приходят объекты с name
                // Или $roleIds = collect($data['roles'])->pluck('id')->toArray(); // Если приходят объекты с id
                $user->syncRoles($roleNamesOrIds); // Используем syncRoles
            }
            if (!empty($data['permissions'])) {
                $permissionNamesOrIds = collect($data['permissions'])->pluck('name')->toArray(); // Предполагаем объекты с name
                $user->syncPermissions($permissionNamesOrIds); // Используем syncPermissions
            }

            DB::commit();
            Log::info('Пользователь успешно создан:', ['id' => $user->id, 'email' => $user->email]);
            return redirect()->route('admin.users.index')->with('success', 'Пользователь успешно создан.');

        } catch (Throwable $e) {
            DB::rollBack();
            Log::error("Ошибка при создании пользователя: " . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return back()->withInput($request->except('password', 'password_confirmation'))->withErrors(['general' => 'Произошла ошибка при создании пользователя.']);
        }
    }

    /**
     * Показ формы редактирования пользователя.
     */
    public function edit(User $user): InertiaResponse // Используем RMB
    {
        // TODO: Проверка прав $this->authorize('update', $user);
        // Загружаем роли и прямые разрешения пользователя
        $user->load(['roles:id,name', 'permissions:id,name']); // Загружаем только ID и имя

        // Загружаем все роли и разрешения для списков выбора
        $roles = Role::select('id', 'name')->orderBy('name')->get();
        $permissions = Permission::select('id', 'name')->orderBy('name')->get();

        return Inertia::render('Admin/Users/Edit', [
            'user' => new UserResource($user), // Передаем ресурс пользователя
            'roles' => $roles,           // Передаем полный список ролей
            'permissions' => $permissions, // Передаем полный список разрешений
        ]);
    }

    /**
     * Обновление пользователя.
     */
    public function update(UpdateUserRequest $request, User $user): RedirectResponse // <--- Используем UpdateUserRequest и RMB
    {
        // authorize() в UpdateUserRequest
        $data = $request->validated();

        try {
            DB::beginTransaction();

            // Обновляем основные данные
            $userData = [
                'name' => $data['name'],
                'email' => $data['email'],
            ];
            // Обновляем пароль, только если он был передан и не пустой
            if (!empty($data['password'])) {
                $userData['password'] = Hash::make($data['password']);
            }
            $user->update($userData);

            // Синхронизируем роли (если массив передан)
            if ($request->has('roles')) {
                $roleNamesOrIds = collect($data['roles'])->pluck('name')->toArray(); // Предполагаем объекты с name
                $user->syncRoles($roleNamesOrIds);
            }

            // Синхронизируем прямые разрешения (если массив передан)
            if ($request->has('permissions')) {
                $permissionNamesOrIds = collect($data['permissions'])->pluck('name')->toArray(); // Предполагаем объекты с name
                $user->syncPermissions($permissionNamesOrIds);
            }

            DB::commit();
            Log::info('Пользователь обновлен:', ['id' => $user->id, 'email' => $user->email]);
            return redirect()->route('admin.users.index')->with('success', 'Пользователь успешно обновлен.');

        } catch (Throwable $e) {
            DB::rollBack();
            Log::error("Ошибка при обновлении пользователя ID {$user->id}: " . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return back()->withInput($request->except('password', 'password_confirmation'))->withErrors(['general' => 'Произошла ошибка при обновлении пользователя.']);
        }
    }

    /**
     * Удаление пользователя.
     */
    public function destroy(User $user): RedirectResponse // Используем RMB
    {
        // TODO: Проверка прав $this->authorize('delete', $user);
        // TODO: Добавить проверку, чтобы нельзя было удалить себя или супер-администратора
        if ($user->id === auth()->id()) {
            return back()->withErrors(['general' => 'Вы не можете удалить свою учетную запись.']);
        }
        if ($user->hasRole('super-admin')) { // Пример проверки роли
            return back()->withErrors(['general' => 'Нельзя удалить супер-администратора.']);
        }

        try {
            // Транзакция не строго обязательна, Spatie позаботится об отсоединении связей
            $user->delete();
            Log::info('Пользователь удален:', ['id' => $user->id, 'email' => $user->email]);
            return redirect()->route('admin.users.index')->with('success', 'Пользователь успешно удален.');
        } catch (Throwable $e) {
            Log::error("Ошибка при удалении пользователя ID {$user->id}: " . $e->getMessage());
            return back()->withErrors(['general' => 'Произошла ошибка при удалении пользователя.']);
        }
    }

}
