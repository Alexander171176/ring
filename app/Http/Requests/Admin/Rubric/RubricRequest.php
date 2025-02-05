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
            'views' => 'nullable|integer',
            'activity' => 'required|boolean',

            'translations' => 'required|array',
            'translations.*.locale' => [
                'required',
                'string',
                'size:2',
                Rule::in(['ru', 'en', 'kz']), // Допустимые языки
            ],
            'translations.*.title' => [
                'required',
                'string',
                'max:255',
                Rule::unique('rubric_translations', 'title')->ignore($this->route('rubric'), 'rubric_id'),
            ],
            'translations.*.url' => [
                'required',
                'string',
                'max:255',
                Rule::unique('rubric_translations', 'url')->ignore($this->route('rubric'), 'rubric_id'),
            ],
            'translations.*.short' => 'nullable|string|max:255',
            'translations.*.description' => 'nullable|string',
            'translations.*.meta_title' => 'nullable|string|max:255',
            'translations.*.meta_keywords' => 'nullable|string|max:255',
            'translations.*.meta_desc' => 'nullable|string|max:255',
        ];
    }

    /**
     * Сообщения об ошибках валидации.
     */
    public function messages(): array
    {
        return [
            'translations.required' => 'Требуется указать переводы для рубрики.',
            'translations.*.locale.required' => 'Язык перевода обязателен.',
            'translations.*.locale.string' => 'Язык должен быть строкой.',
            'translations.*.locale.size' => 'Код языка должен состоять из 2 символов (например, "ru", "en", "kz").',
            'translations.*.locale.in' => 'Допустимые языки: ru, en, kz.',

            'translations.*.title.required' => 'Заголовок рубрики обязателен для заполнения.',
            'translations.*.title.string' => 'Заголовок рубрики должен быть строкой.',
            'translations.*.title.max' => 'Заголовок рубрики не должен превышать 255 символов.',
            'translations.*.title.unique' => 'Рубрика с таким заголовком уже существует.',

            'translations.*.url.required' => 'URL рубрики обязателен для заполнения.',
            'translations.*.url.string' => 'URL рубрики должен быть строкой.',
            'translations.*.url.max' => 'URL рубрики не должен превышать 255 символов.',
            'translations.*.url.unique' => 'Рубрика с таким URL уже существует.',

            'translations.*.short.string' => 'Краткое описание должно быть строкой.',
            'translations.*.short.max' => 'Краткое описание не должно превышать 255 символов.',

            'translations.*.description.string' => 'Описание рубрики должно быть строкой.',

            'translations.*.meta_title.max' => 'Meta заголовок не должен превышать 255 символов.',
            'translations.*.meta_keywords.max' => 'Meta ключевые слова не должны превышать 255 символов.',
            'translations.*.meta_desc.max' => 'Meta описание не должно превышать 255 символов.',

            'sort.integer' => 'Поле сортировки должно быть числом.',
            'views.integer' => 'Количество просмотров должно быть числом.',

            'image_url.image' => 'Файл должен быть изображением.',
            'image_url.mimes' => 'Изображение должно быть в формате jpeg, png, jpg, gif или svg.',
            'image_url.max' => 'Размер изображения не должен превышать 2MB.',

            'activity.required' => 'Поле активности обязательно для заполнения.',
            'activity.boolean' => 'Поле активности должно быть логическим значением.',
        ];
    }
}
