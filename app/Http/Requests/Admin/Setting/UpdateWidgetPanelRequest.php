<?php

namespace App\Http\Requests\Admin\Setting;

use Illuminate\Foundation\Http\FormRequest;

class UpdateWidgetPanelRequest extends FormRequest
{
    public function authorize(): bool
    {
        // TODO: Проверка права 'update settings'
        return $this->user()->can('update settings');
        // return true;
    }

    public function rules(): array
    {
        return [
            // Валидация HEX цвета без #
            'color' => ['required', 'string', 'regex:/^[0-9A-Fa-f]{6}$/'],
            // Валидация прозрачности (число от 0 до 1)
            'opacity' => 'required|numeric|min:0|max:1',
        ];
    }

    public function messages(): array
    {
        return [
            'color.required' => 'Необходимо указать цвет.',
            'color.string' => 'Цвет должен быть строкой.',
            'color.regex' => 'Некорректный формат HEX цвета (ожидается 6 символов 0-9, A-F).',
            'opacity.required' => 'Необходимо указать прозрачность.',
            'opacity.numeric' => 'Прозрачность должна быть числом.',
            'opacity.min' => 'Прозрачность не может быть меньше :min.',
            'opacity.max' => 'Прозрачность не может быть больше :max.',
        ];
    }
}
