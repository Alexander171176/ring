<?php

namespace App\Http\Requests\Admin\Role;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RoleRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:30', Rule::unique('roles', 'name')->ignore($this->role)],
            'permissions' => ['sometimes', 'array']
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Название роли обязательно для заполнения.',
            'name.string' => 'Название роли должно быть строкой.',
            'name.max' => 'Название роли не должно превышать 30 символов.',
            'name.unique' => 'Роль с таким названием уже существует.',

            'permissions.array' => 'Поле "Разрешения" должно быть массивом.',
        ];
    }

}
