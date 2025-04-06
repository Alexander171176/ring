<?php

namespace App\Http\Requests\Admin\Video;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class VideoRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'sort'               => 'nullable|integer',
            'activity'           => 'required|boolean',
            'left'               => 'required|boolean',
            'main'               => 'required|boolean',
            'right'              => 'required|boolean',
            'locale'             => [
                'required',
                'string',
                'size:2',
                Rule::in(['ru', 'en', 'kz']),
            ],
            'title'              => [
                'required',
                'string',
                'max:255',
                Rule::unique('videos', 'title')->ignore($this->route('video')),
            ],
            'url'                => [
                'required',
                'string',
                'max:255',
                Rule::unique('videos', 'url')->ignore($this->route('video')),
            ],
            'short'              => 'nullable|string|max:255',
            'description'        => 'nullable|string',
            'author'             => 'nullable|string|max:255',
            'published_at'       => 'nullable|date',
            'duration'           => 'nullable|integer',
            'source_type'        => 'nullable|string',
            'video_url'          => 'nullable|string',
            'external_video_id'  => 'nullable|string',
            'views'              => 'nullable|integer|min:0',
            'likes'              => 'nullable|integer|min:0',
            'meta_title'         => 'nullable|string|max:255',
            'meta_keywords'      => 'nullable|string|max:255',
            'meta_desc'          => 'nullable|string|max:255',

            // Дополнительные поля для связей
            'sections'           => 'nullable|array',
            'articles'           => 'nullable|array',
            'related_videos'     => 'nullable|array',

            // Валидация массива изображений в таблице в таблице video_images
            'images' => ['sometimes', 'array'],
            'images.*.id' => ['nullable', 'integer', 'exists:video_images,id'],
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

    /**
     * Get the validation messages that apply to the request.
     */
    public function messages(): array
    {
        return [
            'locale.required'           => 'Язык видео обязателен.',
            'locale.string'             => 'Язык должен быть строкой.',
            'locale.size'               => 'Код языка должен состоять из 2 символов (например, "ru", "en", "kz").',
            'locale.in'                 => 'Допустимые языки: ru, en, kz.',

            'title.required'            => 'Название видео обязательно для заполнения.',
            'title.string'              => 'Название видео должно быть строкой.',
            'title.max'                 => 'Название видео не должно превышать 255 символов.',
            'title.unique'              => 'Видео с таким названием уже существует.',

            'url.required'              => 'URL видео обязателен.',
            'url.string'                => 'URL видео должен быть строкой.',
            'url.max'                   => 'URL видео не должен превышать 255 символов.',
            'url.unique'                => 'Видео с таким URL уже существует.',

            'short.string'              => 'Краткое описание должно быть строкой.',
            'short.max'                 => 'Краткое описание не должно превышать 255 символов.',

            'description.string'        => 'Описание должно быть строкой.',

            'author.string'             => 'Имя автора должно быть строкой.',
            'author.max'                => 'Имя автора не должно превышать 255 символов.',

            'views.integer'             => 'Количество просмотров должно быть числом.',
            'views.min'                 => 'Количество просмотров не может быть отрицательным.',

            'likes.integer'             => 'Количество лайков должно быть числом.',
            'likes.min'                 => 'Количество лайков не может быть отрицательным.',

            'meta_title.max'            => 'Meta заголовок не должен превышать 255 символов.',
            'meta_keywords.max'         => 'Meta ключевые слова не должны превышать 255 символов.',
            'meta_desc.max'             => 'Meta описание не должно превышать 255 символов.',

            'sort.integer'              => 'Поле сортировки должно быть числом.',
            'activity.required'         => 'Поле активности обязательно для заполнения.',
            'activity.boolean'          => 'Поле активности должно быть логическим значением.',

            'left.required'             => 'Поле видео в левой колонке обязательно для заполнения.',
            'left.boolean'              => 'Поле видео в левой колонке должно быть логическим значением.',

            'main.required'             => 'Поле главная видео обязательно для заполнения.',
            'main.boolean'              => 'Поле главная видео должно быть логическим значением.',

            'right.required'            => 'Поле видео в правой колонке обязательно для заполнения.',
            'right.boolean'             => 'Поле видео в правой колонке должно быть логическим значением.',

            'sections.array'            => 'Секции должны быть массивом.',
            'articles.array'            => 'Теги должны быть массивом.',
            'related_videos.array'      => 'Список связанных статей должен быть массивом.',

            'images.array'              => 'Изображения должны быть массивом.',
            'images.*.id.exists'        => 'Указанного изображения не существует.',
            'images.*.file.image'       => 'Файл должен быть изображением.',
            'images.*.file.mimes'       => 'Файл должен быть формата jpeg, jpg, png или webp.',
            'images.*.file.max'         => 'Размер файла изображения не должен превышать 10 Мб.',
        ];
    }
}
