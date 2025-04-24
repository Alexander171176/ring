<?php

namespace App\Http\Requests\Admin\User;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'roles' => ['nullable', 'array'],
            'permissions' => ['nullable', 'array'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Имя пользователя обязательно.',
            'email.required' => 'Email обязателен.',
            'email.email' => 'Некорректный формат email.',
            'email.unique' => 'Этот email уже зарегистрирован.',
            'roles.array' => 'Роли должны быть массивом.',
            'permissions.array' => 'Разрешения должны быть массивом.',
        ];
    }
}
