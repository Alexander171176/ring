<?php

namespace App\Http\Requests\Admin\Setting;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SettingRequest extends FormRequest
{
    public function authorize(): bool
    {
        // TODO: Проверка прав
        return true;
    }

    public function rules(): array
    {
        $settingId = $this->route('setting')?->id ?? null;
        // Получаем предполагаемый тип из запроса для условной валидации 'value'
        $type = $this->input('type');

        return [
            // Добавляем Rule::in для известных типов
            'type' => ['nullable', 'string', 'max:255',
                Rule::in(['string', 'text', 'number', 'integer', 'float', 'boolean', 'checkbox', 'json', 'array', 'select'])], // TODO: Дополнить список типов
            'option' => [
                'required','string','max:255',
                Rule::unique('settings', 'option')->ignore($settingId),
            ],
            // Основное правило - nullable, остальное проверяем в зависимости от 'type'
            'value' => [
                'nullable',
                // Добавляем условные правила
                Rule::when($type === 'number' || $type === 'integer' || $type === 'float', ['numeric']),
                Rule::when($type === 'boolean' || $type === 'checkbox', ['boolean']), // Laravel сам обработает '1','0','true','false'
                Rule::when($type === 'json' || $type === 'array', ['json']), // Проверяет строку на валидность JSON
                // Для string/text/select дополнительные правила не нужны, кроме nullable
            ],
            'constant' => [
                'required','string','max:255','regex:/^[A-Z_]+$/',
                Rule::unique('settings', 'constant')->ignore($settingId),
            ],
            'category' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:65535',
            'activity' => 'required|boolean',
        ];
    }

    public function messages(): array
    {
        return array_merge(parent::messages(), [
            'type.in' => 'Выбран недопустимый тип поля.',
            'option.required' => 'Опция обязательна.',
            'option.unique' => 'Настройка с такой опцией уже существует.',
            'value.numeric' => 'Значение должно быть числом для выбранного типа.',
            'value.boolean' => 'Значение должно быть Да/Нет (1/0) для выбранного типа.',
            'value.json' => 'Значение должно быть корректной JSON строкой для выбранного типа.',
            'constant.required' => 'Константа обязательна.',
            'constant.unique' => 'Настройка с такой константой уже существует.',
            'constant.regex' => 'Константа должна содержать только БОЛЬШИЕ латинские буквы и подчеркивания.',
            'activity.required' => 'Поле активности обязательно.',
        ]);
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'activity' => filter_var($this->input('activity'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? false,
        ]);
        // Если тип 'boolean' или 'checkbox' и значение пришло как 'on' (от стандартного checkbox), преобразуем в true
        if (($this->input('type') === 'boolean' || $this->input('type') === 'checkbox') && $this->input('value') === 'on') {
            $this->merge(['value' => true]);
        }
        // Можно добавить другую подготовку, если нужно
    }
}
