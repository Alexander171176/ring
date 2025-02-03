<?php

namespace App\Http\Requests\Admin\Setting;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SettingRequest extends FormRequest
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
            'type' => 'nullable|string|max:255',
            'option' => [
                'required',
                'string',
                'max:255',
                Rule::unique('settings', 'option')->ignore($this->route('parameter')),
            ],
            'value' => 'nullable|string',
            'constant' => [
                'required',
                'string',
                'max:255',
                Rule::unique('settings', 'constant')->ignore($this->route('parameter')),
            ],
            'category' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'activity' => 'boolean',
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
            'type.string' => 'Тип должен быть строкой.',
            'type.max' => 'Тип не должен превышать 255 символов.',

            'option.required' => 'Опция обязательна для заполнения.',
            'option.string' => 'Опция должна быть строкой.',
            'option.max' => 'Опция не должна превышать 255 символов.',
            'option.unique' => 'Настройка с такой опцией уже существует.',

            'value.string' => 'Значение должно быть строкой.',

            'constant.required' => 'Константа обязательна для заполнения.',
            'constant.string' => 'Константа должна быть строкой.',
            'constant.max' => 'Константа не должна превышать 255 символов.',
            'constant.unique' => 'Настройка с такой константой уже существует.',

            'category.string' => 'Категория должна быть строкой.',
            'category.max' => 'Категория не должна превышать 255 символов.',

            'description.string' => 'Описание должно быть строкой.',

            'activity.boolean' => 'Поле активности должно быть логическим значением.',
        ];
    }

}
