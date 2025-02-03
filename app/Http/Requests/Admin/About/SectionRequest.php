<?php

namespace App\Http\Requests\Admin\About;

use Illuminate\Foundation\Http\FormRequest;

class SectionRequest extends FormRequest
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
            'type' => 'nullable|string|max:255',
            'tailwind' => 'nullable|string', // Допускается null значение
            'image' => 'nullable|file|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'title' => 'nullable|string|max:255',
            'content' => 'nullable|string',
            'sort' => 'nullable|integer|min:0',
            'activity' => 'nullable|boolean'
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
            'type.string' => 'Тип должен быть строкой.',
            'type.max' => 'Тип не должен превышать 255 символов.',

            'tailwind.string' => 'Tailwind-класс должен быть строкой.',

            'image.image' => 'Файл должен быть изображением.',
            'image.mimes' => 'Допустимые форматы изображений: jpeg, png, jpg, gif, svg.',
            'image.max' => 'Изображение не должно превышать 2048 килобайт.',

            'title.string' => 'Заголовок должен быть строкой.',
            'title.max' => 'Заголовок не должен превышать 255 символов.',

            'content.string' => 'Содержание должно быть строкой.',

            'sort.integer' => 'Порядок сортировки должен быть целым числом.',
            'sort.min' => 'Порядок сортировки не может быть отрицательным.',

            'activity.boolean' => 'Поле активности должно быть логическим.'
        ];
    }
}
