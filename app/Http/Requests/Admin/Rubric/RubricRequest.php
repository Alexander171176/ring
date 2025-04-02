<?php

namespace App\Http\Requests\Admin\Rubric;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RubricRequest extends FormRequest
{
    /**
     * Определяет, авторизован ли пользователь для выполнения запроса.
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
            'icon' => 'nullable|string',
            'activity' => 'required|boolean',

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
                Rule::unique('rubrics', 'title')->ignore($this->route('rubric')),
            ],
            'url' => [
                'required',
                'string',
                'max:255',
                Rule::unique('rubrics', 'url')->ignore($this->route('rubric')),
            ],
            'short' => 'nullable|string|max:255',
            'meta_title' => 'nullable|string|max:255',
            'meta_keywords' => 'nullable|string|max:255',
            'meta_desc' => 'nullable|string|max:255',
        ];
    }

    /**
     * Сообщения об ошибках валидации.
     */
    public function messages(): array
    {
        return [
            'icon.string' => 'svg должно быть строкой.',

            'locale.required' => 'Язык рубрики обязателен.',
            'locale.string' => 'Язык должен быть строкой.',
            'locale.size' => 'Код языка должен состоять из 2 символов (например, "ru", "en", "kz").',
            'locale.in' => 'Допустимые языки: ru, en, kz.',

            'title.required' => 'Заголовок рубрики обязателен.',
            'title.string' => 'Заголовок рубрики должен быть строкой.',
            'title.max' => 'Заголовок рубрики не должен превышать 255 символов.',
            'title.unique' => 'Рубрика с таким заголовком уже существует.',

            'url.required' => 'URL рубрики обязателен.',
            'url.string' => 'URL рубрики должен быть строкой.',
            'url.max' => 'URL рубрики не должен превышать 255 символов.',
            'url.unique' => 'Рубрика с таким URL уже существует.',

            'short.string' => 'Краткое описание должно быть строкой.',
            'short.max' => 'Краткое описание не должно превышать 255 символов.',

            'meta_title.max' => 'Meta заголовок не должен превышать 255 символов.',
            'meta_keywords.max' => 'Meta ключевые слова не должны превышать 255 символов.',
            'meta_desc.max' => 'Meta описание не должно превышать 255 символов.',

            'sort.integer' => 'Поле сортировки должно быть числом.',
            'activity.required' => 'Поле активности обязательно для заполнения.',
            'activity.boolean' => 'Поле активности должно быть логическим значением.',
        ];
    }
}
