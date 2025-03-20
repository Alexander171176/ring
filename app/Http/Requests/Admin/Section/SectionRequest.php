<?php

namespace App\Http\Requests\Admin\Section;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SectionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Возвращает правила валидации для запроса.
     */
    public function rules(): array
    {
        return [
            'sort' => 'nullable|integer',
            'activity' => 'required|boolean',
            'icon' => 'nullable|string|max:255',
            'locale' => [
                'required',
                'string',
                'size:2',
                Rule::in(['ru', 'en', 'kz']),
            ],
            'title' => [
                'required',
                'string',
                'max:255',
                Rule::unique('articles', 'title')->ignore($this->route('article')),
            ],
            'short' => 'nullable|string|max:255',

            // Связи
            'rubrics' => ['sometimes', 'array'],

        ];
    }

    /**
     * Сообщения об ошибках валидации.
     */
    public function messages(): array
    {
        return [
            'locale.required' => 'Язык секции обязателен.',
            'locale.string' => 'Язык должен быть строкой.',
            'locale.size' => 'Код языка должен состоять из 2 символов (например, "ru", "en", "kz").',
            'locale.in' => 'Допустимые языки: ru, en, kz.',

            'title.required' => 'Название секции обязательно для заполнения.',
            'title.string' => 'Название секции должно быть строкой.',
            'title.max' => 'Название секции не должно превышать 255 символов.',
            'title.unique' => 'Секция с таким Названием уже существует.',

            'short.string' => 'Краткое описание должно быть строкой.',
            'short.max' => 'Краткое описание не должно превышать 255 символов.',

            'sort.integer' => 'Поле сортировки должно быть числом.',
            'activity.required' => 'Поле активности обязательно для заполнения.',
            'activity.boolean' => 'Поле активности должно быть логическим значением.',

            'rubrics.array' => 'Рубрики должны быть массивом.',

        ];
    }
}
