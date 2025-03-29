<?php

namespace App\Http\Requests\Admin\Article;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ArticleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'sort' => 'nullable|integer',
            'activity' => 'required|boolean',
            'left' => 'required|boolean',
            'main' => 'required|boolean',
            'right' => 'required|boolean',
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
            'sections' => ['sometimes', 'array'],
            'tags' => ['sometimes', 'array'],
            'related_articles' => ['sometimes', 'array'],

            // Валидация массива изображений
            'images' => ['sometimes', 'array'],
            'images.*.id' => ['nullable', 'integer', 'exists:article_images,id'],
            'images.*.order' => ['nullable', 'integer'],
            'images.*.alt' => ['nullable', 'string', 'max:255'],
            'images.*.caption' => ['nullable', 'string', 'max:255'],
            'images.*.file' => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp', 'max:10240'], // 10MB

            // Если файла и ID нет одновременно, то ошибка:
            'images.*' => ['array', function ($attr, $value, $fail) {
                if (empty($value['id']) && empty($value['file'])) {
                    $fail("Изображение должно иметь либо загруженный файл, либо ID существующего изображения.");
                }
            }],
        ];
    }

    // сообщения валидации полей
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

            'left.required' => 'Поле новость в левой колонке обязательно для заполнения.',
            'left.boolean' => 'Поле новость в левой колонке должно быть логическим значением.',

            'main.required' => 'Поле главная новость обязательно для заполнения.',
            'main.boolean' => 'Поле главная новость должно быть логическим значением.',

            'right.required' => 'Поле новость в правой колонке обязательно для заполнения.',
            'right.boolean' => 'Поле новость в правой колонке должно быть логическим значением.',

            'sections.array' => 'Секции должны быть массивом.',
            'tags.array' => 'Теги должны быть массивом.',
            'related_articles.array' => 'Список связанных статей должен быть массивом.',

            'images.array' => 'Изображения должны быть массивом.',
            'images.*.id.exists' => 'Указанного изображения не существует.',
            'images.*.file.image' => 'Файл должен быть изображением.',
            'images.*.file.mimes' => 'Файл должен быть формата jpeg, jpg, png или webp.',
            'images.*.file.max' => 'Размер файла изображения не должен превышать 10 Мб.',
        ];
    }
}
