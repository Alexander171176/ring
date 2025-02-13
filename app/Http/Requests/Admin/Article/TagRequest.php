<?php

namespace App\Http\Requests\Admin\Article;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TagRequest extends FormRequest
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
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('tags', 'name')->ignore($this->route('tag')),
            ],
            'slug' => [
                'required',
                'string',
                'max:255',
                Rule::unique('tags', 'slug')->ignore($this->route('tag')),
            ],
            'locale' => [
                'required',
                'string',
                'size:2',
                Rule::in(['ru', 'en', 'kz']),
            ],
        ];
    }

    /**
     * Сообщения об ошибках валидации.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Название тега обязательно.',
            'name.string' => 'Название тега должно быть строкой.',
            'name.max' => 'Название тега не должно превышать 255 символов.',
            'name.unique' => 'Тег с таким названием уже существует.',

            'slug.required' => 'Slug тега обязателен.',
            'slug.string' => 'Slug тега должен быть строкой.',
            'slug.max' => 'Slug тега не должен превышать 255 символов.',
            'slug.unique' => 'Тег с таким slug уже существует.',

            'locale.required' => 'Язык тега обязателен.',
            'locale.string' => 'Язык должен быть строкой.',
            'locale.size' => 'Код языка должен состоять из 2 символов (например, "ru", "en", "kz").',
            'locale.in' => 'Допустимые языки: ru, en, kz.',
        ];
    }
}
