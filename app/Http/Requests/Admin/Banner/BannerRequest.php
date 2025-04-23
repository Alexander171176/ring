<?php

namespace App\Http\Requests\Admin\Banner;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BannerRequest extends FormRequest
{
    public function authorize(): bool
    {
        // TODO: Добавить проверку прав
        return true;
    }

    public function rules(): array
    {
        // Получаем ID баннера из маршрута для ignore()
        $bannerId = $this->route('banner')?->id ?? null;

        return [
            'sort' => 'nullable|integer|min:0', // Добавим min:0
            'activity' => 'required|boolean',
            'left' => 'required|boolean',
            'right' => 'required|boolean',
            'title' => [
                'required',
                'string',
                'max:255',
                // Уникальность title глобальная (не зависит от locale, т.к. его нет в модели/миграции)
                Rule::unique('banners', 'title')->ignore($bannerId), // <--- КОРРЕКТНО (если нет locale)
            ],
            // 'link' => 'nullable|string', // В миграции тип text, уберем max если нужно
            'link' => 'nullable|string|max:65535', // <--- Увеличено max для TEXT
            'short' => 'nullable|string|max:255',
            'comment' => 'nullable|string|max:255',

            // Валидация связи с секциями
            'sections' => ['nullable', 'array'], // <--- Изменено на nullable
            'sections.*.id' => ['required_with:sections', 'integer', 'exists:sections,id'], // <--- Добавлено

            // Валидация массива изображений
            'images' => ['nullable', 'array'], // <--- Изменено на nullable
            'images.*.id' => [
                'nullable',
                'integer',
                Rule::exists('banner_images', 'id'), // Проверяем в banner_images
                Rule::prohibitedIf(fn() => $this->isMethod('POST')), // Запрещаем ID при создании
            ],
            'images.*.order' => ['nullable', 'integer', 'min:0'], // Добавлено min:0
            'images.*.alt' => ['nullable', 'string', 'max:255'],
            'images.*.caption' => ['nullable', 'string', 'max:255'],
            'images.*.file'      => [
                'nullable',
                'required_without:images.*.id',
                'file',
                'image',
                'mimes:jpeg,jpg,png,gif,svg,webp',
                'max:10240',
            ],

            // Удаляем кастомную проверку, она больше не нужна
            // 'images.*' => ['array', function ($attr, $value, $fail) { ... }],

            // Валидация удаляемых изображений
            'deletedImages' => ['sometimes', 'array'], // <--- Добавлено
            'deletedImages.*' => ['integer', 'exists:banner_images,id'] // <--- Добавлено
        ];
    }

    public function messages(): array
    {
        // TODO: Исправить сообщения: ссылаются на "статью", а не "баннер"
        return [
            'title.required' => 'Название баннера обязательно для заполнения.', // Исправлено
            'title.string' => 'Название баннера должно быть строкой.', // Исправлено
            'title.max' => 'Название баннера не должно превышать :max символов.', // Исправлено
            'title.unique' => 'Баннер с таким Названием уже существует.', // Исправлено

            'link.string' => 'Ссылка должна быть строкой.', // Исправлено
            'link.max' => 'Ссылка слишком длинная.', // Добавлено

            'short.string' => 'Краткое описание должно быть строкой.',
            'short.max' => 'Краткое описание не должно превышать :max символов.',

            'comment.string' => 'Комментарий должен быть строкой.', // Исправлено
            'comment.max' => 'Комментарий не должен превышать :max символов.', // Исправлено

            'sort.integer' => 'Поле сортировки должно быть числом.',
            'sort.min' => 'Поле сортировки не может быть отрицательным.', // Добавлено
            'activity.required' => 'Поле активности обязательно для заполнения.',
            'activity.boolean' => 'Поле активности должно быть логическим значением.',

            'left.required' => 'Поле "В левой колонке" обязательно для заполнения.',
            'left.boolean' => 'Поле "В левой колонке" должно быть логическим значением.',

            'right.required' => 'Поле "В правой колонке" обязательно для заполнения.',
            'right.boolean' => 'Поле "В правой колонке" должно быть логическим значением.',

            'sections.array' => 'Секции должны быть массивом.',
            'sections.*.id.required_with' => 'ID секции обязателен.', // Добавлено
            'sections.*.id.integer' => 'ID секции должен быть числом.', // Добавлено
            'sections.*.id.exists' => 'Выбрана несуществующая секция.', // Добавлено

            'images.array' => 'Изображения должны быть массивом.',
            'images.*.id.integer' => 'ID изображения должен быть числом.', // Добавлено
            'images.*.id.exists' => 'Указанного изображения баннера не существует.', // Уточнено
            'images.*.id.prohibited' => 'ID изображения нельзя передавать при создании.', // Добавлено
            'images.*.order.integer' => 'Порядок изображения должен быть числом.', // Добавлено
            'images.*.order.min' => 'Порядок изображения не может быть отрицательным.', // Добавлено
            'images.*.alt.string' => 'Alt текст изображения должен быть строкой.',
            'images.*.alt.max' => 'Alt текст не должен превышать :max символов.',
            'images.*.caption.string' => 'Подпись изображения должен быть строкой.',
            'images.*.caption.max' => 'Подпись не должен превышать :max символов.',
            'images.*.file.required' => 'Файл изображения обязателен для новых изображений.', // Добавлено
            'images.*.file.file' => 'Проблема с загрузкой файла изображения.',
            'images.*.file.image' => 'Файл должен быть изображением.',
            'images.*.file.mimes' => 'Файл должен быть формата jpeg, jpg, png, gif, svg или webp.', // Расширено
            'images.*.file.max' => 'Размер файла изображения не должен превышать :max Кб.', // Уточнено Кб
            'images.*.file.required_without' => 'Файл изображения обязателен для новых изображений.',

            'deletedImages.array' => 'Список удаляемых изображений должен быть массивом.', // Добавлено
            'deletedImages.*.integer' => 'ID удаляемого изображения должен быть числом.', // Добавлено
            'deletedImages.*.exists' => 'Попытка удалить несуществующее изображение баннера.', // Уточнено
        ];
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
            'left' => filter_var($this->input('left'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? false,
            'right' => filter_var($this->input('right'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? false,
        ]);
    }
}
