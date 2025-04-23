<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSortRequest extends FormRequest {

    public function authorize(): bool
    {
        // TODO: Заменить на реальную проверку права 'moderator'
        // return $this->user()->can('moderator');
        return true;
    }

    public function rules(): array
    {
        return [
            // Просто проверяем, что значение передано и является строкой
            // все возможные типы сортировок проверяет SettingController в методе getAllowedSortValuesFor
            'value' => ['string', 'max:50'], // Добавим max для безопасности
        ];
    }

    public function messages(): array
    {
        return [
            'value.string' => 'Тип сортировки должен быть строкой.',
            'value.max' => 'Значение сортировки слишком длинное.', // Добавили сообщение для max
        ];
    }
}
