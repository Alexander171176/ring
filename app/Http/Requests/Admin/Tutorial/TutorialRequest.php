<?php

namespace App\Http\Requests\Admin\Tutorial;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TutorialRequest extends FormRequest
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
                Rule::unique('tutorials', 'title')->ignore($this->route('tutorial')),
            ],
            'url' => [
                'required',
                'string',
                'max:255',
                Rule::unique('tutorials', 'url')->ignore($this->route('tutorial')),
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
            'sort.integer' => 'Поле сортировки должно быть числом.',

            'icon.string' => 'Иконка должна быть строкой.',

            'title.required' => 'Заголовок обязателен для заполнения.',
            'title.string' => 'Заголовок должен быть строкой.',
            'title.max' => 'Заголовок не должен превышать 255 символов.',
            'title.unique' => 'Урок с таким заголовком уже существует.',

            'url.required' => 'URL обязателен для заполнения.',
            'url.string' => 'URL должен быть строкой.',
            'url.max' => 'URL не должен превышать 255 символов.',
            'url.unique' => 'Урок с таким URL уже существует.',

            'short.string' => 'Краткое описание должно быть строкой.',
            'short.max' => 'Краткое описание не должно превышать 255 символов.',

            'description.string' => 'Описание должно быть строкой.',

            'views.integer' => 'Количество просмотров должно быть числом.',

            'image_url.image' => 'Файл должен быть изображением.',
            'image_url.mimes' => 'Изображение должно быть одного из следующих типов: jpeg, png, jpg, gif, svg.',
            'image_url.max' => 'Размер изображения не должен превышать 2048 KB.',

            'seo_title.string' => 'SEO заголовок должен быть строкой.',
            'seo_title.max' => 'SEO заголовок не должен превышать 255 символов.',

            'seo_alt.string' => 'SEO alt текст должен быть строкой.',
            'seo_alt.max' => 'SEO alt текст не должен превышать 255 символов.',

            'meta_title.string' => 'Meta title должен быть строкой.',
            'meta_title.max' => 'Meta title не должен превышать 255 символов.',

            'meta_keywords.string' => 'Meta keywords должны быть строкой.',
            'meta_keywords.max' => 'Meta keywords не должны превышать 255 символов.',

            'meta_desc.string' => 'Meta описание должно быть строкой.',
            'meta_desc.max' => 'Meta описание не должно превышать 255 символов.',

            'activity.required' => 'Поле активности обязательно для заполнения.',
            'activity.boolean' => 'Поле активности должно быть логическим значением.',
        ];
    }

}
