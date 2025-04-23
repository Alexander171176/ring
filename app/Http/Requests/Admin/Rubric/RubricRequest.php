<?php

namespace App\Http\Requests\Admin\Rubric;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
// use Illuminate\Support\Str; // Если будете использовать Str::slug в prepareForValidation

class RubricRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // TODO: Заменить на реальную проверку прав доступа
        // if ($this->isMethod('POST')) return $this->user()->can('create rubrics');
        // if ($this->isMethod('PUT') || $this->isMethod('PATCH')) return $this->user()->can('update', $this->route('rubric'));
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        // Получаем ID рубрики из маршрута, если это обновление
        $rubricId = $this->route('rubric')?->id ?? null;

        return [
            'sort' => 'nullable|integer|min:0', // Добавлено min:0
            'activity' => 'required|boolean',
            'icon' => 'nullable|string|max:65535', // Увеличено max для TEXT
            'locale' => [
                'required',
                'string',
                'size:2',
                Rule::in(['ru', 'en', 'kz']), // TODO: Актуализировать список локалей
            ],
            'title' => [
                'required',
                'string',
                'max:255',
                // Исправлена проверка уникальности: учитываем 'locale', игнорируем текущий ID
                Rule::unique('rubrics')->where(function ($query) {
                    return $query->where('locale', $this->input('locale'));
                })->ignore($rubricId),
            ],
            'url' => [
                'required',
                'string',
                'max:500', // Увеличено max
                'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/', // Добавлено regex
                // Исправлена проверка уникальности: учитываем 'locale', игнорируем текущий ID
                Rule::unique('rubrics')->where(function ($query) {
                    return $query->where('locale', $this->input('locale'));
                })->ignore($rubricId),
            ],
            'short' => 'nullable|string|max:255',
            'description' => 'nullable|string', // <--- Добавлено правило
            'views' => 'nullable|integer|min:0', // <--- Добавлено правило

            'meta_title' => 'nullable|string|max:255',
            'meta_keywords' => 'nullable|string|max:255',
            'meta_desc' => 'nullable|string', // Убрано max:255

            // Если секции передаются в ЭТОМ запросе (обычно нет, управляется из SectionRequest)
            /*
            'sections' => ['nullable', 'array'],
            'sections.*.id' => ['required_with:sections', 'integer', 'exists:sections,id'],
            */
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return array_merge(parent::messages(), [
            'locale.required' => 'Язык рубрики обязателен.',
            'locale.in' => 'Допустимые языки: :values.',

            'title.required' => 'Название рубрики обязательно.',
            'title.max' => 'Название рубрики не должно превышать :max символов.',
            'title.unique' => 'Рубрика с таким Названием и Языком уже существует.', // Исправлено

            'url.required' => 'URL рубрики обязателен.',
            'url.max' => 'URL рубрики не должен превышать :max символов.', // Исправлено
            'url.regex' => 'URL должен содержать только латинские буквы, цифры и дефисы.', // Добавлено
            'url.unique' => 'Рубрика с таким URL и Языком уже существует.', // Исправлено

            'short.max' => 'Краткое описание не должно превышать :max символов.',
            'description.string' => 'Описание должно быть строкой.', // Добавлено

            'icon.string' => 'Иконка должна быть строкой.',
            'icon.max' => 'Содержимое иконки слишком длинное.', // Исправлено

            'views.integer' => 'Количество просмотров должно быть числом.', // Добавлено
            'views.min' => 'Количество просмотров не может быть отрицательным.', // Добавлено

            'meta_title.max' => 'Meta заголовок не должен превышать :max символов.',
            'meta_keywords.max' => 'Meta ключевые слова не должны превышать :max символов.',
            'meta_desc.string' => 'Meta описание должно быть строкой.', // Исправлено

            'sort.integer' => 'Поле сортировки должно быть числом.',
            'sort.min' => 'Поле сортировки не может быть отрицательным.', // Добавлено
            'activity.required' => 'Поле активности обязательно для заполнения.',
            'activity.boolean' => 'Поле активности должно быть логическим значением.',

            // Сообщения для секций, если они здесь валидируются
            /*
            'sections.array' => 'Секции должны быть массивом.',
            'sections.*.id.required_with' => 'ID секции обязателен.',
            'sections.*.id.integer' => 'ID секции должен быть числом.',
            'sections.*.id.exists' => 'Выбрана несуществующая секция.',
            */
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

        // Автоматическая генерация URL, если он пуст
        if (empty($this->input('url')) && !empty($this->input('title'))) {
            // TODO: Убедиться, что функция transliterate или Str::slug доступна
            // $this->merge(['url' => Str::slug($this->input('title'))]);
        } else if (!empty($this->input('url'))) {
            // Очищаем URL
            // $this->merge(['url' => Str::slug($this->input('url'))]);
        }
    }
}
