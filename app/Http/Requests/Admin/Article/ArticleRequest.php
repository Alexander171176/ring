<?php

namespace App\Http\Requests\Admin\Article;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ArticleRequest extends FormRequest
{
    public function authorize(): bool
    {
        // TODO: Добавить проверку прав доступа (например, $this->user()->can('create articles') или 'update articles')
        return true;
    }

    public function rules(): array
    {
        // Получаем ID статьи из маршрута, если это обновление
        $articleId = $this->route('article')?->id ?? null; // Используем null safe оператор и null coalescing

        return [
            'sort'               => 'nullable|integer|min:0',
            'activity'           => 'required|boolean',
            'left'               => 'required|boolean',
            'main'               => 'required|boolean',
            'right'              => 'required|boolean',
            'locale'             => [
                'required','string','size:2',
                Rule::in(['ru','en','kz']),
            ],
            'title'              => [
                'required','string','max:255',
                Rule::unique('articles')->where(fn($q) => $q->where('locale', $this->input('locale')))
                    ->ignore($articleId),
            ],
            'url'                => [
                'required','string','max:500',
                'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
                Rule::unique('articles')->where(fn($q) => $q->where('locale', $this->input('locale')))
                    ->ignore($articleId),
            ],
            'short'              => 'nullable|string|max:255',
            'description'        => 'nullable|string',
            'author'             => 'nullable|string|max:255',
            'published_at'       => 'nullable|date',
            'views'              => 'nullable|integer|min:0',
            'likes'              => 'nullable|integer|min:0',

            'meta_title'         => 'nullable|string|max:255',
            'meta_keywords'      => 'nullable|string|max:255',
            'meta_desc'          => 'nullable|string',

            'sections'           => ['nullable','array'],
            'sections.*.id'      => ['required_with:sections','integer','exists:sections,id'],

            'tags'               => ['nullable','array'],
            'tags.*.id'          => ['required_with:tags','integer','exists:tags,id'],

            'related_articles'   => ['nullable','array'],
            'related_articles.*.id' => ['required_with:related_articles','integer','exists:articles,id'],

            'images'             => ['nullable','array'],
            'images.*.id'        => [
                'nullable','integer',
                Rule::exists('article_images','id'),
                Rule::prohibitedIf(fn() => $this->isMethod('POST')),
            ],
            'images.*.order'     => ['nullable','integer','min:0'],
            'images.*.alt'       => ['nullable','string','max:255'],
            'images.*.caption'   => ['nullable','string','max:255'],
            'images.*.file'      => [
                'nullable',
                'required_without:images.*.id',
                'file',
                'image',
                'mimes:jpeg,jpg,png,gif,svg,webp',
                'max:10240',
            ],

            'deletedImages'      => ['sometimes','array'],
            'deletedImages.*'    => ['integer','exists:article_images,id'],
        ];
    }

    // Сообщения валидации полей (оставляем ваши, но можно добавить для published_at, связей, deletedImages)
    public function messages(): array
    {
        return [
            'locale.required' => 'Язык статьи обязателен.',
            'locale.string' => 'Язык должен быть строкой.',
            'locale.size' => 'Код языка должен состоять из 2 символов (например, "ru", "en", "kz").',
            'locale.in' => 'Допустимые языки: ru, en, kz.',

            'title.required' => 'Название статьи обязательно для заполнения.',
            'title.string' => 'Название статьи должно быть строкой.',
            'title.max' => 'Название статьи не должно превышать 255 символов.',
            'title.unique' => 'Статья с таким Названием и Языком уже существует.', // Уточнено

            'url.required' => 'URL статьи обязателен.',
            'url.string' => 'URL статьи должен быть строкой.',
            'url.max' => 'URL статьи не должен превышать 500 символов.', // Исправлено
            'url.regex' => 'URL должен содержать только латинские буквы, цифры и дефисы.', // Добавлено
            'url.unique' => 'Статья с таким URL и Языком уже существует.', // Уточнено

            'published_at.date' => 'Некорректный формат даты публикации.', // Добавлено

            'short.string' => 'Краткое описание должно быть строкой.',
            'short.max' => 'Краткое описание не должно превышать 255 символов.',

            'description.string' => 'Описание должно быть строкой.',

            'author.string' => 'Имя автора должно быть строкой.',
            'author.max' => 'Имя автора не должно превышать 255 символов.',

            'views.integer' => 'Количество просмотров должно быть числом.',
            'views.min' => 'Количество просмотров не может быть отрицательным.',

            'likes.integer' => 'Количество лайков должно быть числом.',
            'likes.min' => 'Количество лайков не может быть отрицательным.',

            'meta_title.max' => 'Meta заголовок не должен превышать 255 символов.',
            'meta_keywords.max' => 'Meta ключевые слова не должны превышать 255 символов.',
            'meta_desc.string' => 'Meta описание должно быть строкой.', // Исправлено

            'sort.integer' => 'Поле сортировки должно быть числом.',
            'sort.min' => 'Поле сортировки не может быть отрицательным.', // Добавлено
            'activity.required' => 'Поле активности обязательно для заполнения.',
            'activity.boolean' => 'Поле активности должно быть логическим значением.',

            'left.required' => 'Поле "В левой колонке" обязательно для заполнения.', // Уточнено
            'left.boolean' => 'Поле "В левой колонке" должно быть логическим значением.',

            'main.required' => 'Поле "Главная новость" обязательно для заполнения.', // Уточнено
            'main.boolean' => 'Поле "Главная новость" должно быть логическим значением.',

            'right.required' => 'Поле "В правой колонке" обязательно для заполнения.', // Уточнено
            'right.boolean' => 'Поле "В правой колонке" должно быть логическим значением.',

            'sections.array' => 'Секции должны быть массивом.',
            'sections.*.id.required_with' => 'ID секции обязателен.',
            'sections.*.id.integer' => 'ID секции должен быть числом.',
            'sections.*.id.exists' => 'Выбрана несуществующая секция.',

            'tags.array' => 'Теги должны быть массивом.',
            'tags.*.id.required_with' => 'ID тега обязателен.',
            'tags.*.id.integer' => 'ID тега должен быть числом.',
            'tags.*.id.exists' => 'Выбран несуществующий тег.',

            'related_articles.array' => 'Список связанных статей должен быть массивом.',
            'related_articles.*.id.required_with' => 'ID связанной статьи обязателен.',
            'related_articles.*.id.integer' => 'ID связанной статьи должен быть числом.',
            'related_articles.*.id.exists' => 'Выбрана несуществующая связанная статья.',

            'images.array' => 'Изображения должны быть массивом.',
            'images.*.id.integer' => 'ID изображения должен быть числом.',
            'images.*.id.exists' => 'Указанного изображения не существует.',
            'images.*.id.prohibited' => 'ID изображения нельзя передавать при создании.', // Добавлено
            'images.*.order.integer' => 'Порядок изображения должен быть числом.',
            'images.*.order.min' => 'Порядок изображения не может быть отрицательным.',
            'images.*.alt.string' => 'Alt текст изображения должен быть строкой.',
            'images.*.alt.max' => 'Alt текст не должен превышать 255 символов.',
            'images.*.caption.string' => 'Подпись изображения должен быть строкой.',
            'images.*.caption.max' => 'Подпись не должен превышать 255 символов.',
            'images.*.file.required' => 'Файл изображения обязателен для новых изображений.', // Добавлено
            'images.*.file.file' => 'Проблема с загрузкой файла изображения.', // Добавлено
            'images.*.file.image' => 'Файл должен быть изображением.',
            'images.*.file.mimes' => 'Файл должен быть формата jpeg, jpg, png, gif, svg или webp.', // Добавлены форматы
            'images.*.file.max' => 'Размер файла изображения не должен превышать 10 Мб.',
            'images.*.file.required_without' => 'Файл изображения обязателен для новых изображений.',

            'deletedImages.array' => 'Список удаляемых изображений должен быть массивом.',
            'deletedImages.*.integer' => 'ID удаляемого изображения должен быть числом.',
            'deletedImages.*.exists' => 'Попытка удалить несуществующее изображение.',
        ];
    }

    /**
     * Prepare the data for validation.
     *
     * @return void
     */
    protected function prepareForValidation(): void
    {
        // Преобразуем значения чекбоксов, если они приходят как 'true'/'false' или on/off
        $this->merge([
            'activity' => filter_var($this->input('activity'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? false,
            'left' => filter_var($this->input('left'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? false,
            'main' => filter_var($this->input('main'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? false,
            'right' => filter_var($this->input('right'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? false,
        ]);

        // Автоматическая генерация URL, если он пуст и есть title
        if (empty($this->input('url')) && !empty($this->input('title'))) {
            // TODO: Убедиться, что функция transliterate подключена или использовать Str::slug
            // $this->merge(['url' => Str::slug($this->input('title'))]);
            // $this->merge(['url' => transliterate($this->input('title'))]);
        } else if (!empty($this->input('url'))) {
            // Очищаем URL от лишнего, если он введен вручную
            // $this->merge(['url' => Str::slug($this->input('url'))]);
        }
    }
}
