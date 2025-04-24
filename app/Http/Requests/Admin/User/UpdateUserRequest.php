<?php

namespace App\Http\Requests\Admin\User;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends FormRequest
{
//    public function authorize(): bool
//    {
//        return true;
//    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($this->user),
            ],
            'roles' => ['sometimes', 'array'],
            'permissions' => ['sometimes', 'array'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Имя пользователя обязательно для заполнения.',
            'email.required' => 'Email обязателен для заполнения.',
            'email.email' => 'Укажите корректный формат email.',
            'email.unique' => 'Такой email уже зарегистрирован.',
            'roles.array' => 'Роли должны быть массивом.',
            'permissions.array' => 'Разрешения должны быть массивом.',
        ];
    }
}
