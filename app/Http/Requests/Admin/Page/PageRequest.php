<?php

namespace App\Http\Requests\Admin\Page;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PageRequest extends FormRequest
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
            'title' => [
                'required',
                'string',
                'max:255',
                Rule::unique('pages', 'title')->ignore($this->route('page')),
            ],
            'url' => [
                'required',
                'string',
                'max:255',
                Rule::unique('pages', 'url')->ignore($this->route('page')),
            ],
            'slug' => [
                'required',
                'string',
                'max:255',
                Rule::unique('pages', 'slug')->ignore($this->route('page')),
            ],
            'content' => 'nullable|string',
            'meta_title' => 'nullable|string|max:255',
            'meta_keywords' => 'nullable|string|max:255',
            'meta_desc' => 'nullable|string|max:255',
            'activity' => 'required|boolean',
            'print_in_menu' => 'required|boolean',
            'parent_id' => [
                'nullable',
                'integer',
                Rule::exists('pages', 'id'),
            ],
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
            'sort.integer' => 'Порядок сортировки должен быть числом.',

            'title.required' => 'Заголовок обязателен для заполнения.',
            'title.string' => 'Заголовок должен быть строкой.',
            'title.max' => 'Заголовок не должен превышать 255 символов.',
            'title.unique' => 'Страница с таким заголовком уже существует.',

            'url.required' => 'URL обязателен для заполнения.',
            'url.string' => 'URL должен быть строкой.',
            'url.max' => 'URL не должен превышать 255 символов.',
            'url.unique' => 'Страница с таким URL уже существует.',

            'slug.required' => 'Slug обязателен для заполнения.',
            'slug.string' => 'Slug должен быть строкой.',
            'slug.max' => 'Slug не должен превышать 255 символов.',
            'slug.unique' => 'Страница с таким slug уже существует.',

            'content.string' => 'Контент должен быть строкой.',

            'meta_title.string' => 'Meta title должен быть строкой.',
            'meta_title.max' => 'Meta title не должен превышать 255 символов.',

            'meta_keywords.string' => 'Meta keywords должны быть строкой.',
            'meta_keywords.max' => 'Meta keywords не должны превышать 255 символов.',

            'meta_desc.string' => 'Meta описание должно быть строкой.',
            'meta_desc.max' => 'Meta описание не должно превышать 255 символов.',

            'activity.required' => 'Поле активности обязательно для заполнения.',
            'activity.boolean' => 'Поле активности должно быть логическим.',

            'print_in_menu.required' => 'Поле отображения в меню обязательно для заполнения.',
            'print_in_menu.boolean' => 'Поле отображения в меню должно быть логическим.',

            'parent_id.integer' => 'ID родительской страницы должно быть числом.',
            'parent_id.exists' => 'Указанная родительская страница не существует.',
        ];
    }

}
