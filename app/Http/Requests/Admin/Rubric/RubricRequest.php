<?php

namespace App\Http\Requests\Admin\Rubric;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RubricRequest extends FormRequest
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
            'icon' => 'nullable|string',
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
            'description' => 'nullable|string',
            'views' => 'nullable|integer',
            'image_url' => 'nullable|file|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'seo_title' => 'nullable|string|max:255',
            'seo_alt' => 'nullable|string|max:255',
            'meta_title' => 'nullable|string|max:255',
            'meta_keywords' => 'nullable|string|max:255',
            'meta_desc' => 'nullable|string|max:255',
            'activity' => 'required|boolean',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'title.required' => 'Заголовок рубрики обязателен для заполнения.',
            'title.string' => 'Заголовок рубрики должен быть строкой.',
            'title.max' => 'Заголовок рубрики не должен превышать 255 символов.',
            'title.unique' => 'Рубрика с таким заголовком уже существует.',

            'url.required' => 'URL рубрики обязателен для заполнения.',
            'url.string' => 'URL рубрики должен быть строкой.',
            'url.max' => 'URL рубрики не должен превышать 255 символов.',
            'url.unique' => 'Рубрика с таким URL уже существует.',

            'short.string' => 'Краткое описание должно быть строкой.',
            'short.max' => 'Краткое описание не должно превышать 255 символов.',

            'views.integer' => 'Количество просмотров должно быть целым числом.',

            'image_url.image' => 'Файл должен быть изображением.',
            'image_url.mimes' => 'Изображение должно быть в формате jpeg, png, jpg, gif или svg.',
            'image_url.max' => 'Размер изображения не должен превышать 2MB.',

            'seo_title.max' => 'SEO заголовок не должен превышать 255 символов.',
            'seo_alt.max' => 'SEO alt текст не должен превышать 255 символов.',
            'meta_title.max' => 'Meta заголовок не должен превышать 255 символов.',
            'meta_keywords.max' => 'Meta ключевые слова не должны превышать 255 символов.',
            'meta_desc.max' => 'Meta описание не должно превышать 255 символов.',

            'activity.required' => 'Поле активности обязательно для заполнения.',
            'activity.boolean' => 'Поле активности должно быть логическим значением.',
        ];
    }

}
