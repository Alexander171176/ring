<?php

namespace App\Http\Requests\Admin\Section;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SectionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // TODO: Заменить на реальную проверку прав доступа
        // Например:
        // if ($this->isMethod('POST')) {
        //     return $this->user()->can('create sections');
        // }
        // if ($this->isMethod('PUT') || $this->isMethod('PATCH')) {
        //     return $this->user()->can('update', $this->route('section'));
        // }
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        // Получаем ID секции из маршрута, если это обновление
        $sectionId = $this->route('section')?->id ?? null;

        return [
            'sort' => 'nullable|integer|min:0',
            'activity' => 'required|boolean',
            'icon' => 'nullable|string|max:65535', // Изменено на max:65535, если может быть SVG кодом (text в миграции)
            // 'icon_file' => ['nullable', 'image', 'mimes:svg,png,jpg', 'max:512'], // <--- ЕСЛИ иконка - загружаемый ФАЙЛ, добавить правила для него
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
                // Исправлена проверка уникальности: таблица 'sections', учитываем 'locale', игнорируем текущий ID
                Rule::unique('sections')->where(function ($query) {
                    return $query->where('locale', $this->input('locale'));
                })->ignore($sectionId),
            ],
            // Раскомментируйте, если добавили поле 'url' в миграцию
            /*
            'url' => [
                'required',
                'string',
                'max:255', // Или 500, как в миграции, если она там есть
                'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
                Rule::unique('sections')->where(function ($query) {
                    return $query->where('locale', $this->input('locale'));
                })->ignore($sectionId),
            ],
            */
            'short' => 'nullable|string|max:255',
            'description' => 'nullable|string', // <--- Добавлено правило для description

            // Валидация связи с рубриками
            'rubrics' => ['nullable', 'array'],
            'rubrics.*.id' => ['required_with:rubrics', 'integer', 'exists:rubrics,id'], // <--- Добавлено: проверяем ID рубрик

        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        // Берем предыдущие сообщения и добавляем/исправляем нужные
        return array_merge(parent::messages(), [
            'locale.required' => 'Язык секции обязателен.',
            'locale.string' => 'Язык должен быть строкой.',
            'locale.size' => 'Код языка должен состоять из 2 символов.',
            'locale.in' => 'Допустимые языки: :values.', // Используем :values

            'title.required' => 'Название секции обязательно для заполнения.',
            'title.string' => 'Название секции должно быть строкой.',
            'title.max' => 'Название секции не должно превышать :max символов.',
            'title.unique' => 'Секция с таким Названием и Языком уже существует.', // Уточнено

            // Раскомментируйте, если добавили url
            /*
           'url.required' => 'URL секции обязателен.',
           'url.string' => 'URL секции должен быть строкой.',
           'url.max' => 'URL секции не должен превышать :max символов.',
           'url.regex' => 'URL должен содержать только латинские буквы, цифры и дефисы.',
           'url.unique' => 'Секция с таким URL и Языком уже существует.',
           */

            'short.string' => 'Краткое описание должно быть строкой.',
            'short.max' => 'Краткое описание не должно превышать :max символов.',

            'description.string' => 'Описание должно быть строкой.', // Добавлено

            'sort.integer' => 'Поле сортировки должно быть числом.',
            'sort.min' => 'Поле сортировки не может быть отрицательным.', // Добавлено
            'activity.required' => 'Поле активности обязательно для заполнения.',
            'activity.boolean' => 'Поле активности должно быть логическим значением.',

            'icon.string' => 'Иконка должна быть строкой.',
            'icon.max' => 'Иконка не должна превышать :max символов.',
            // 'icon_file.image' => 'Файл иконки должен быть изображением.', // Если иконка - файл
            // 'icon_file.mimes' => 'Допустимые форматы иконки: :values.',
            // 'icon_file.max' => 'Размер файла иконки не должен превышать :max Кб.',

            'rubrics.array' => 'Рубрики должны быть массивом.',
            'rubrics.*.id.required_with' => 'ID рубрики обязателен.', // Добавлено
            'rubrics.*.id.integer' => 'ID рубрики должен быть числом.', // Добавлено
            'rubrics.*.id.exists' => 'Выбрана несуществующая рубрика.', // Добавлено
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

        // Автоматическая генерация URL, если он есть и пуст
        /*
        if ($this->has('url') && empty($this->input('url')) && !empty($this->input('title'))) {
            // TODO: Убедиться, что функция transliterate или Str::slug доступна
            // $this->merge(['url' => Str::slug($this->input('title'))]);
        } else if ($this->has('url') && !empty($this->input('url'))) {
             // $this->merge(['url' => Str::slug($this->input('url'))]);
        }
        */
    }
}
