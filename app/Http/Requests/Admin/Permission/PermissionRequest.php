<?php

namespace App\Http\Requests\Admin\Permission;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PermissionRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:30', Rule::unique('permissions', 'name')
                ->ignore($this->permission)],
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
            'name.required' => 'Имя разрешения обязательно для заполнения.',
            'name.string' => 'Имя разрешения должно быть строкой.',
            'name.max' => 'Имя разрешения не должно превышать 30 символов.',
            'name.unique' => 'Разрешение с таким именем уже существует.',
        ];
    }

}
