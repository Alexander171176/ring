<?php

namespace App\Http\Requests\Admin\User;

use Illuminate\Foundation\Http\FormRequest;
use App\Actions\Fortify\PasswordValidationRules; // Используем трейт для правил пароля
use App\Models\User;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class StoreUserRequest extends FormRequest
{
    // Используем трейт Fortify/Jetstream для стандартных правил создания пароля
    // Он обычно добавляет 'required', 'string', Password::defaults(), 'confirmed'
    use PasswordValidationRules;

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // TODO: Заменить на реальную проверку права 'create users'
        // return $this->user()->can('create users');
        return true; // Временно разрешаем
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        // Получаем имена таблиц из конфига Spatie
        $rolesTable = config('permission.table_names.roles', 'roles');
        $permissionsTable = config('permission.table_names.permissions', 'permissions');

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'lowercase', // Приводим email к нижнему регистру
                'email',
                'max:255',
                Rule::unique(User::class), // Простая проверка уникальности для нового пользователя
            ],
            // Правило 'password' берется из трейта PasswordValidationRules
            // Обычно оно включает 'required', 'string', Password::defaults(), 'confirmed'
            'password' => $this->passwordRules(),
            // 'password_confirmation' валидируется правилом 'confirmed' из трейта

            'roles' => ['nullable', 'array'],
            'roles.*.id' => [
                'required_with:roles', // ID обязателен, если массив roles передан и не пуст
                'integer',
                Rule::exists($rolesTable, 'id') // Проверяем ID роли
            ],

            'permissions' => ['nullable', 'array'],
            'permissions.*.id' => [
                'required_with:permissions', // ID обязателен, если массив permissions передан и не пуст
                'integer',
                Rule::exists($permissionsTable, 'id') // Проверяем ID разрешения
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
        // Возвращаем полный набор сообщений
        return array_merge(parent::messages(), [
            'name.required' => 'Имя пользователя обязательно.',
            'name.string' => 'Имя пользователя должно быть строкой.',
            'name.max' => 'Имя пользователя не должно превышать :max символов.',

            'email.required' => 'Email обязателен.',
            'email.string' => 'Email должен быть строкой.',
            'email.lowercase' => 'Email должен быть в нижнем регистре.', // Хотя prepareForValidation это делает
            'email.email' => 'Введите корректный email адрес.',
            'email.max' => 'Email не должен превышать :max символов.',
            'email.unique' => 'Пользователь с таким Email уже существует.',

            // Сообщения для правил пароля из трейта PasswordValidationRules
            // Обычно включают сообщения для required, string, min, confirmed, и правил сложности
            'password.required' => 'Пароль обязателен.',
            'password.string' => 'Пароль должен быть строкой.',
            'password.confirmed' => 'Подтверждение пароля не совпадает.',
            // Сообщения для Password::defaults() (длина, буквы, цифры, символы) обычно определены глобально в lang файлах Laravel

            'password_confirmation.required' => 'Необходимо подтверждение пароля.', // Если confirmed используется

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

        // Преобразуем массив объектов ролей/разрешений в массив ID для валидации 'exists'
        if ($this->has('roles')) {
            $this->merge([
                'roles' => collect($this->input('roles'))
                    ->filter(fn($role) => !empty($role['id']))
                    ->map(fn($role) => ['id' => $role['id']])
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
