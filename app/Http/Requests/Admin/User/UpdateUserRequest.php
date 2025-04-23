<?php

namespace App\Http\Requests\Admin\User;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\User; // Импортируем модель User
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;       // Импортируем модель Role
use Spatie\Permission\Models\Permission; // Импортируем модель Permission
use Illuminate\Validation\Rules\Password; // <--- Импортируем класс Password

class UpdateUserRequest extends FormRequest
{
    // Трейт PasswordValidationRules здесь не нужен, определяем правила явно
    // use PasswordValidationRules;

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // TODO: Заменить на реальную проверку прав доступа
        // Например, использовать Policy:
        // return $this->user()->can('update', $this->route('user'));
        return true; // Временно разрешаем
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        // Получаем ID редактируемого пользователя из маршрута
        $userId = $this->route('user')?->id;
        if (!$userId) {
            // Если ID не найден в маршруте (маловероятно при правильной настройке),
            // можно выбросить исключение или вернуть пустой массив правил,
            // но лучше полагаться на то, что Route Model Binding работает.
            // Для Rule::unique это приведет к ошибке, если $userId будет null.
            // Можно добавить проверку $this->route('user') в authorize()
        }

        // Получаем имена таблиц из конфига Spatie
        $rolesTable = config('permission.table_names.roles', 'roles');
        $permissionsTable = config('permission.table_names.permissions', 'permissions');

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'lowercase', // Приводим email к нижнему регистру перед валидацией
                'email',
                'max:255',
                Rule::unique(User::class)->ignore($userId), // Игнорируем текущего пользователя
            ],
            'password' => [
                'nullable', // Пароль необязателен при обновлении
                'string',
                Password::defaults()->sometimes(), // Правила сложности Laravel, только если поле передано
                'confirmed', // <--- ДОБАВИТЬ, если в форме ЕСТЬ поле password_confirmation
            ],
            // Добавляем правило для password_confirmation, если оно нужно
            'password_confirmation' => [
                // Required только если password не пустой
                Rule::requiredIf(fn () => !empty($this->input('password'))),
                'string',
            ],
            'roles' => ['nullable', 'array'],
            // Проверяем, что каждый элемент - существующий ID роли
            'roles.*.id' => [
                'required_with:roles', // ID обязателен, если передан массив roles
                'integer',
                Rule::exists($rolesTable, 'id')
            ],
            'permissions' => ['nullable', 'array'],
            // Проверяем, что каждый элемент - существующий ID разрешения
            'permissions.*.id' => [
                'required_with:permissions',
                'integer',
                Rule::exists($permissionsTable, 'id')
            ],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return array_merge(parent::messages(), [
            'name.required' => 'Имя пользователя обязательно.',
            'name.max' => 'Имя пользователя не должно превышать :max символов.',

            'email.required' => 'Email обязателен.',
            'email.email' => 'Введите корректный email адрес.',
            'email.max' => 'Email не должен превышать :max символов.',
            'email.unique' => 'Пользователь с таким Email уже существует.',

            // Сообщения для стандартных правил пароля (из Password::defaults()) обычно встроены
            'password.confirmed' => 'Подтверждение пароля не совпадает.', // Если используете confirmed

            'password_confirmation.required' => 'Необходимо подтверждение пароля.', // Если используете confirmed

            'roles.array' => 'Роли должны быть массивом.',
            'roles.*.id.required_with' => 'ID роли обязателен.',
            'roles.*.id.integer' => 'ID роли должен быть числом.',
            'roles.*.id.exists' => 'Выбрана несуществующая роль (ID: :value).',

            'permissions.array' => 'Разрешения должны быть массивом.',
            'permissions.*.id.required_with' => 'ID разрешения обязателен.',
            'permissions.*.id.integer' => 'ID разрешения должен быть числом.',
            'permissions.*.id.exists' => 'Выбрано несуществующее разрешение (ID: :value).',
        ]);
    }

    /**
     * Prepare the data for validation.
     *
     * @return void
     */
    protected function prepareForValidation(): void
    {
        // Приводим email к нижнему регистру перед валидацией уникальности
        if ($this->has('email')) {
            $this->merge([
                'email' => strtolower($this->input('email')),
            ]);
        }

        // Если пароль не заполнен (пустая строка или null), удаляем его и подтверждение из запроса
        // чтобы он не перезаписался пустым значением и не требовалось подтверждение
        if ($this->isNotFilled('password')) { // isNotFilled проверяет на null и пустую строку ''
            $this->request->remove('password');
            $this->request->remove('password_confirmation'); // Удаляем и подтверждение
        }

        // Преобразуем массив объектов ролей/разрешений в массив ID для валидации 'exists'
        // (Предполагаем, что vue-multiselect присылает массив объектов с ключом 'id')
        if ($this->has('roles')) {
            $this->merge([
                // Оставляем только объекты с непустым ID, затем извлекаем ID
                'roles' => collect($this->input('roles'))
                    ->filter(fn($role) => !empty($role['id']))
                    ->map(fn($role) => ['id' => $role['id']]) // Оставляем массив массивов для валидации *.id
                    ->all(),
            ]);
        }
        if ($this->has('permissions')) {
            $this->merge([
                'permissions' => collect($this->input('permissions'))
                    ->filter(fn($perm) => !empty($perm['id']))
                    ->map(fn($perm) => ['id' => $perm['id']])
                    ->all(),
            ]);
        }
    }
}
