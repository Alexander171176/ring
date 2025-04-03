<?php

namespace App\Http\Requests\Admin\Banner;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BannerRequest extends FormRequest
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
            'sort' => 'nullable|integer',
            'activity' => 'required|boolean',
            'left' => 'required|boolean',
            'right' => 'required|boolean',
            'title' => [
                'required',
                'string',
                'max:255',
                Rule::unique('banners', 'title')->ignore($this->route('banner')),
            ],
            'link' => 'nullable|string',
            'short' => 'nullable|string|max:255',
            'comment' => 'nullable|string|max:255',

            // Связи
            'sections' => ['sometimes', 'array'],

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
            'title.required' => 'Название статьи обязательно для заполнения.',
            'title.string' => 'Название статьи должно быть строкой.',
            'title.max' => 'Название статьи не должно превышать 255 символов.',
            'title.unique' => 'Статья с таким Названием уже существует.',

            'link.string' => 'ссылка должна быть строкой.',

            'short.string' => 'Краткое описание должно быть строкой.',
            'short.max' => 'Краткое описание не должно превышать 255 символов.',

            'comment.string' => 'Краткое описание должно быть строкой.',
            'comment.max' => 'Краткое описание не должно превышать 255 символов.',

            'sort.integer' => 'Поле сортировки должно быть числом.',
            'activity.required' => 'Поле активности обязательно для заполнения.',
            'activity.boolean' => 'Поле активности должно быть логическим значением.',

            'left.required' => 'Поле новость в левой колонке обязательно для заполнения.',
            'left.boolean' => 'Поле новость в левой колонке должно быть логическим значением.',

            'right.required' => 'Поле новость в правой колонке обязательно для заполнения.',
            'right.boolean' => 'Поле новость в правой колонке должно быть логическим значением.',

            'sections.array' => 'Секции должны быть массивом.',

            'images.array' => 'Изображения должны быть массивом.',
            'images.*.id.exists' => 'Указанного изображения не существует.',
            'images.*.file.image' => 'Файл должен быть изображением.',
            'images.*.file.mimes' => 'Файл должен быть формата jpeg, jpg, png или webp.',
            'images.*.file.max' => 'Размер файла изображения не должен превышать 10 Мб.',
        ];
    }
}
