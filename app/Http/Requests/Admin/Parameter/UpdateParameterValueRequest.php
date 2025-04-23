<?php

namespace App\Http\Requests\Admin\Parameter;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule; // Добавляем Rule

class UpdateParameterValueRequest extends FormRequest
{
    public function authorize(): bool
    {
        $parameter = $this->route('parameter'); // Получаем модель Setting
        // TODO: Проверка прав - может ли пользователь обновлять этот КОНКРЕТНЫЙ параметр?
        return $this->user()->can('update', $parameter);
        // return true;
    }

    public function rules(): array
    {
        // Получаем тип из модели, которую редактируем
        $parameterType = $this->route('parameter')?->type ?? 'string'; // По умолчанию строка, если что-то пошло не так

        return [
            'value' => [
                'nullable',
                // Добавляем условные правила на основе ТИПА МОДЕЛИ
                Rule::when($parameterType === 'number' || $parameterType === 'integer' || $parameterType === 'float', ['numeric']),
                Rule::when($parameterType === 'boolean' || $parameterType === 'checkbox', ['boolean']),
                Rule::when($parameterType === 'json' || $parameterType === 'array', ['json']),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'value.numeric' => 'Значение должно быть числом для этого параметра.',
            'value.boolean' => 'Значение должно быть Да/Нет (1/0) для этого параметра.',
            'value.json' => 'Значение должно быть корректной JSON строкой для этого параметра.',
        ];
    }

    protected function prepareForValidation(): void
    {
        // Получаем тип из модели
        $parameterType = $this->route('parameter')?->type;
        // Преобразуем 'on' в true для булевых типов
        if (($parameterType === 'boolean' || $parameterType === 'checkbox') && $this->input('value') === 'on') {
            $this->merge(['value' => true]);
        }
        // Преобразуем пустые строки в null, если тип не строка/текст
        // if (!in_array($parameterType, ['string', 'text']) && $this->input('value') === '') {
        //     $this->merge(['value' => null]);
        // }
    }
}
