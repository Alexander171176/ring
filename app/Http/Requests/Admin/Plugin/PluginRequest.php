<?php

namespace App\Http\Requests\Admin\Plugin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PluginRequest extends FormRequest
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
            'sort' => 'nullable|integer',
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('plugins', 'name')->ignore($this->route('plugin')),
            ],
            'version' => 'nullable|string|max:255',
            'icon' => 'nullable|string',
            'description' => 'nullable|string',
            'readme' => 'nullable|string',
            'options' => 'nullable|string',
            'code' => 'nullable|string|max:255',
            'templates' => 'nullable|string|max:255',
            'activity' => 'required|boolean',
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
            'sort.integer' => 'Поле сортировки должно быть числом.',

            'name.required' => 'Имя плагина обязательно для заполнения.',
            'name.string' => 'Имя плагина должно быть строкой.',
            'name.max' => 'Имя плагина не должно превышать 255 символов.',
            'name.unique' => 'Плагин с таким именем уже существует.',

            'version.string' => 'Версия плагина должна быть строкой.',
            'version.max' => 'Версия плагина не должна превышать 255 символов.',

            'icon.string' => 'Иконка должна быть строкой.',

            'description.string' => 'Описание должно быть строкой.',

            'readme.string' => 'README должно быть строкой.',

            'options.string' => 'Опции должны быть строкой.',

            'code.string' => 'Код должен быть строкой.',
            'code.max' => 'Код не должен превышать 255 символов.',

            'templates.string' => 'Шаблоны должны быть строкой.',
            'templates.max' => 'Шаблоны не должны превышать 255 символов.',

            'activity.required' => 'Поле активности обязательно для заполнения.',
            'activity.boolean' => 'Поле активности должно быть логическим.',
        ];
    }

}
