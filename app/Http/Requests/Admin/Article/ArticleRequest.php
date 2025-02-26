<?php

namespace App\Http\Requests\Admin\Article;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ArticleRequest extends FormRequest
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
                Rule::unique('articles', 'title')->ignore($this->route('article')),
            ],
            'url' => [
                'required',
                'string',
                'max:255',
                Rule::unique('articles', 'url')->ignore($this->route('article')),
            ],
            'short' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'author' => 'nullable|string|max:255',
            'views' => 'nullable|integer|min:0',
            'likes' => 'nullable|integer|min:0',

            'meta_title' => 'nullable|string|max:255',
            'meta_keywords' => 'nullable|string|max:255',
            'meta_desc' => 'nullable|string|max:255',

            // Связи
            'rubrics' => ['sometimes', 'array'],
            'tags' => ['sometimes', 'array'],

            // Обновленная валидация изображений
            'images.*' => [
                'nullable',
                function ($attribute, $value, $fail) {
                    if (is_array($value) && array_key_exists('id', $value) && !is_numeric($value['id'])) {
                        $fail('ID изображения должен быть числом.');
                    }
                    if (is_array($value) && array_key_exists('file', $value) && !$value['file'] instanceof \Illuminate\Http\UploadedFile) {
                        $fail('Загружаемый файл должен быть изображением.');
                    }
                    if (!isset($value['id']) && !isset($value['file'])) {
                        $fail('Каждое изображение должно содержать либо ID, либо загруженный файл.');
                    }
                },
            ],

        ];
    }

    /**
     * Сообщения об ошибках валидации.
     */
    public function messages(): array
    {
        return [
            'locale.required' => 'Язык статьи обязателен.',
            'locale.string' => 'Язык должен быть строкой.',
            'locale.size' => 'Код языка должен состоять из 2 символов (например, "ru", "en", "kz").',
            'locale.in' => 'Допустимые языки: ru, en, kz.',

            'title.required' => 'Название статьи обязательно для заполнения.',
            'title.string' => 'Название статьи должно быть строкой.',
            'title.max' => 'Название статьи не должно превышать 255 символов.',
            'title.unique' => 'Статья с таким Названием уже существует.',

            'url.required' => 'URL статьи обязателен.',
            'url.string' => 'URL статьи должен быть строкой.',
            'url.max' => 'URL статьи не должен превышать 255 символов.',
            'url.unique' => 'Статья с таким URL уже существует.',

            'short.string' => 'Краткое описание должно быть строкой.',
            'short.max' => 'Краткое описание не должно превышать 255 символов.',

            'description.string' => 'Описание должно быть строкой.',

            'author.string' => 'Имя автора должно быть строкой.',
            'author.max' => 'Имя автора не должно превышать 255 символов.',

            'views.integer' => 'Количество просмотров должно быть числом.',
            'views.min' => 'Количество просмотров не может быть отрицательным.',

            'likes.integer' => 'Количество лайков должно быть числом.',
            'likes.min' => 'Количество лайков не может быть отрицательным.',

            'meta_title.max' => 'Meta заголовок не должен превышать 255 символов.',
            'meta_keywords.max' => 'Meta ключевые слова не должны превышать 255 символов.',
            'meta_desc.max' => 'Meta описание не должно превышать 255 символов.',

            'sort.integer' => 'Поле сортировки должно быть числом.',
            'activity.required' => 'Поле активности обязательно для заполнения.',
            'activity.boolean' => 'Поле активности должно быть логическим значением.',

            'rubrics.array' => 'Рубрики должны быть массивом.',
            'tags.array' => 'Теги должны быть массивом.',

        ];
    }
}
