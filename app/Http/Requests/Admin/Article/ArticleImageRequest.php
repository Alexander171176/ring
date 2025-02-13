<?php

namespace App\Http\Requests\Admin\Article;

use Illuminate\Foundation\Http\FormRequest;

class ArticleImageRequest extends FormRequest
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
            'path' => 'required|string|max:255',
            'alt' => 'nullable|string|max:255',
            'caption' => 'nullable|string|max:255',
        ];
    }

    /**
     * Сообщения об ошибках валидации.
     */
    public function messages(): array
    {
        return [
            'path.required' => 'Путь к изображению обязателен.',
            'path.string' => 'Путь к изображению должен быть строкой.',
            'path.max' => 'Путь к изображению не должен превышать 255 символов.',

            'alt.string' => 'Alt текст должен быть строкой.',
            'alt.max' => 'Alt текст не должен превышать 255 символов.',

            'caption.string' => 'Подпись должна быть строкой.',
            'caption.max' => 'Подпись не должна превышать 255 символов.',
        ];
    }
}
