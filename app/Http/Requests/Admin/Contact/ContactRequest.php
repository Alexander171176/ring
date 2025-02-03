<?php

namespace App\Http\Requests\Admin\Contact;

use Illuminate\Foundation\Http\FormRequest;

class ContactRequest extends FormRequest
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
            'image' => 'nullable|file|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'tailwind' => 'nullable|string', // Допускается null значение
            'title' => 'nullable|string|max:255',
            'content' => 'nullable|string'
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
            'image.image' => 'Файл должен быть изображением.',
            'image.mimes' => 'Допустимые форматы изображений: jpeg, png, jpg, gif, svg.',
            'image.max' => 'Изображение не должно превышать 2048 килобайт.',

            'tailwind.string' => 'Поле Tailwind должно быть строкой.',

            'title.string' => 'Заголовок должен быть строкой.',
            'title.max' => 'Заголовок не должен превышать 255 символов.',

            'content.string' => 'Содержание должно быть строкой.',
        ];
    }

}
