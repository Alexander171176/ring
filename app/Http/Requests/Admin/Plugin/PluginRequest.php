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
        // TODO: Заменить на реальную проверку прав доступа
        // if ($this->isMethod('POST')) return $this->user()->can('create plugins');
        // if ($this->isMethod('PUT') || $this->isMethod('PATCH')) return $this->user()->can('update', $this->route('plugin'));
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        // Получаем ID плагина из маршрута (предполагаем имя параметра 'plugin')
        $pluginId = $this->route('plugin')?->id ?? null;

        return [
            'sort' => 'nullable|integer|min:0', // Добавлено min:0
            'activity' => 'required|boolean',
            'icon' => 'nullable|string|max:65535', // Увеличено max для TEXT
            'name' => [
                'required',
                'string',
                'max:255',
                // Уникальность name глобальная
                Rule::unique('plugins', 'name')->ignore($pluginId),
            ],
            'version' => 'nullable|string|max:255',
            'code' => [
                'nullable', // Или 'required', если код обязателен
                'string',
                'max:255',
                'regex:/^[a-z0-9_]+$/i', // Пример: буквы, цифры, подчеркивание (регистронезависимо)
                // TODO: Раскомментировать, если 'code' должен быть уникальным
                // Rule::unique('plugins', 'code')->ignore($pluginId),
            ],
            // Используем правило 'json' для валидации JSON строки
            'options' => 'nullable|json', // <--- ИСПРАВЛЕНО
            'description' => 'nullable|string|max:65535', // Увеличено max для TEXT
            'readme' => 'nullable|string', // Убрано max
            'templates' => 'nullable|string|max:255',
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
            'sort.integer' => 'Поле сортировки должно быть числом.',
            'sort.min' => 'Поле сортировки не может быть отрицательным.',

            'name.required' => 'Имя плагина обязательно.',
            'name.max' => 'Имя плагина не должно превышать :max символов.',
            'name.unique' => 'Плагин с таким именем уже существует.',

            'version.max' => 'Версия плагина не должна превышать :max символов.',

            'icon.string' => 'Иконка должна быть строкой.',
            'icon.max' => 'Содержимое иконки слишком длинное.',

            'description.string' => 'Описание должно быть строкой.',
            'description.max' => 'Описание слишком длинное.',

            'readme.string' => 'README должно быть строкой.',

            'options.json' => 'Опции должны быть корректной JSON строкой.', // Добавлено

            'code.string' => 'Код должен быть строкой.',
            'code.max' => 'Код не должен превышать :max символов.',
            'code.regex' => 'Код может содержать только латинские буквы, цифры и подчеркивание.', // Добавлено
            // 'code.unique' => 'Плагин с таким кодом уже существует.', // Если code уникален

            'templates.max' => 'Поле шаблонов не должно превышать :max символов.',

            'activity.required' => 'Поле активности обязательно.',
            'activity.boolean' => 'Поле активности должно быть логическим.',
        ]);
    }

    /**
     * Prepare the data for validation.
     *
     * @return void
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'activity' => filter_var($this->input('activity'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? false,
        ]);

        // Если options приходят не как JSON строка, а как массив/объект из Vue,
        // Laravel FormRequest обычно сам преобразует их для валидации 'json'.
        // Если же приходит строка, но не JSON, правило 'json' выдаст ошибку.
        // Дополнительная подготовка здесь обычно не нужна для 'options'.
    }
}
