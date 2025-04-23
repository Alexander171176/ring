<?php

namespace App\Http\Requests\Admin\Tag;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
// use Illuminate\Support\Str; // Если будете использовать Str::slug в prepareForValidation

class TagRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // TODO: Заменить на реальную проверку прав доступа
        // if ($this->isMethod('POST')) return $this->user()->can('create tags');
        // if ($this->isMethod('PUT') || $this->isMethod('PATCH')) return $this->user()->can('update', $this->route('tag'));
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        // Получаем ID тега из маршрута, если это обновление
        $tagId = $this->route('tag')?->id ?? null;

        return [
            'sort' => 'nullable|integer|min:0', // <--- Добавлено
            'activity' => 'required|boolean',    // <--- Добавлено
            'locale' => [
                'required',
                'string',
                'size:2',
                Rule::in(['ru', 'en', 'kz']), // TODO: Актуализировать список локалей
            ],
            'name' => [
                'required',
                'string',
                'max:255',
                // Исправлена проверка уникальности: учитываем 'locale', игнорируем текущий ID
                Rule::unique('tags')->where(function ($query) {
                    return $query->where('locale', $this->input('locale'));
                })->ignore($tagId),
            ],
            'slug' => [
                'required',
                'string',
                'max:255', // Slug обычно не такой длинный, но соответствует миграции
                'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/', // <--- Добавлено: Валидация формата slug
                // Исправлена проверка уникальности: учитываем 'locale', игнорируем текущий ID
                Rule::unique('tags')->where(function ($query) {
                    return $query->where('locale', $this->input('locale'));
                })->ignore($tagId),
            ],
            'short' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'views' => 'nullable|integer|min:0', // <--- Добавлено

            'meta_title' => 'nullable|string|max:255',
            'meta_keywords' => 'nullable|string|max:255',
            'meta_desc' => 'nullable|string', // Убрано max:255
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        // Используем array_merge для сохранения стандартных сообщений Laravel, если нужно
        return array_merge(parent::messages(), [
            'locale.required' => 'Язык тега обязателен.',
            'locale.in' => 'Допустимые языки: :values.',

            'name.required' => 'Название тега обязательно.',
            'name.max' => 'Название тега не должно превышать :max символов.',
            'name.unique' => 'Тег с таким Названием и Языком уже существует.', // Исправлено

            'slug.required' => 'Slug тега обязателен.',
            'slug.max' => 'Slug тега не должен превышать :max символов.',
            'slug.regex' => 'Slug должен содержать только латинские буквы, цифры и дефисы.', // Добавлено
            'slug.unique' => 'Тег с таким Slug и Языком уже существует.',    // Исправлено

            'short.max' => 'Краткое описание не должно превышать :max символов.',
            'description.string' => 'Описание должно быть строкой.',

            'meta_title.max' => 'Meta заголовок не должен превышать :max символов.',
            'meta_keywords.max' => 'Meta ключевые слова не должны превышать :max символов.',
            'meta_desc.string' => 'Meta описание должно быть строкой.', // Исправлено

            'sort.integer' => 'Поле сортировки должно быть числом.',
            'sort.min' => 'Поле сортировки не может быть отрицательным.', // Добавлено
            'activity.required' => 'Поле активности обязательно для заполнения.',
            'activity.boolean' => 'Поле активности должно быть логическим значением.', // Добавлено

            'views.integer' => 'Количество просмотров должно быть числом.', // Добавлено
            'views.min' => 'Количество просмотров не может быть отрицательным.', // Добавлено
        ]);
    }

    /**
     * Prepare the data for validation.
     *
     * @return void
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'activity' => filter_var($this->input('activity'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? false,
        ]);

        // Автоматическая генерация Slug, если он пуст и модель его не генерирует сама
        if (empty($this->input('slug')) && !empty($this->input('name'))) {
            // TODO: Убедиться, что функция transliterate или Str::slug доступна
            // $this->merge(['slug' => Str::slug($this->input('name'))]);
            // $this->merge(['slug' => transliterate($this->input('name'))]);
        } else if (!empty($this->input('slug'))) {
            // Очищаем Slug от лишнего, если введен вручную
            // $this->merge(['slug' => Str::slug($this->input('slug'))]);
        }
    }
}
