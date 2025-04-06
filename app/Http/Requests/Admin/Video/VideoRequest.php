<?php

namespace App\Http\Requests\Admin\Video;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class VideoRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Обычно true для админки, или добавить логику прав доступа
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        // Определяем, это создание или обновление
        $isCreating = $this->isMethod('POST');
        $videoId = $this->route('video'); // Получаем ID из маршрута при обновлении

        return [
            'sort' => 'nullable|integer|min:0',
            'activity' => 'required|boolean',
            'left' => 'required|boolean',
            'main' => 'required|boolean',
            'right' => 'required|boolean',
            'locale' => 'required|string|max:2',
            'title' => 'required|string|max:255',
            // Уникальность URL проверяем только при создании или если URL изменился при обновлении
            'url' => [
                'required',
                'string',
                'max:255',
                'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/', // Правило для slug
                $isCreating
                    ? Rule::unique('videos', 'url')
                    : Rule::unique('videos', 'url')->ignore($videoId),
            ],
            'short' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'author' => 'nullable|string|max:255',
            'published_at' => 'nullable|date',
            'duration' => 'nullable|integer|min:0',
            'source_type' => ['required', 'string', Rule::in(['local', 'youtube', 'vimeo', 'code'])],
            'video_url' => [ // Поле URL теперь необязательно, если тип 'local' и есть файл
                'nullable',
                Rule::requiredIf(fn () => !in_array($this->input('source_type'), ['local', 'youtube', 'vimeo'])), // Обязательно для 'code'
                'string',
                'max:65535', // Для кода может быть длинным
                // Можно добавить 'url' валидацию, если нужно, но для кода это не подходит
            ],
            'external_video_id' => [ // ID/Ссылка для внешних сервисов
                'nullable',
                Rule::requiredIf(fn () => in_array($this->input('source_type'), ['youtube', 'vimeo'])),
                'string',
                'max:255',
            ],
            'video_file' => [ // Файл для локального видео
                'nullable',
                // Обязательно, если тип 'local' И нет ID (т.е. при создании)
                // или если тип 'local' и нет video_url (если вы решите его использовать для отображения имени файла)
                Rule::requiredIf(fn() => $this->input('source_type') === 'local' && $isCreating),
                'file',
                'mimes:mp4,mov,ogg,qt,webm,avi', // Укажите разрешенные MIME типы или расширения
                'max:102400', // Максимальный размер в килобайтах (например, 100MB) - настройте на сервере!
            ],
            'views' => 'nullable|integer|min:0',
            'likes' => 'nullable|integer|min:0',
            'meta_title' => 'nullable|string|max:160',
            'meta_keywords' => 'nullable|string|max:255',
            'meta_desc' => 'nullable|string|max:255',

            // Дополнительные поля для связей
            'sections.*.id' => 'sometimes|integer|exists:sections,id',
            'sections.*.title' => 'sometimes|string', // Добавлено для vue-multiselect
            'articles' => 'nullable|array',
            'articles.*.id' => 'sometimes|integer|exists:articles,id',
            'articles.*.title' => 'sometimes|string', // Добавлено для vue-multiselect
            'related_videos' => 'nullable|array',
            'related_videos.*.id' => 'sometimes|integer|exists:videos,id',
            'related_videos.*.title' => 'sometimes|string', // Добавлено для vue-multiselect

            // Валидация массива изображений в таблице в таблице video_images
            'images' => 'nullable|array',
            'images.*.id' => 'nullable|integer|exists:video_images,id',
            'images.*.file' => 'nullable|required_without:images.*.id|image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048',
            'images.*.order' => 'nullable|integer',
            'images.*.alt' => 'nullable|string|max:255',
            'images.*.caption' => 'nullable|string|max:255',
            'deletedImages' => 'nullable|array',
            'deletedImages.*' => 'integer|exists:video_images,id',
        ];
    }

    /**
     * Get the validation messages that apply to the request.
     */
    public function messages(): array
    {
        return [
            'locale.required'           => 'Язык видео обязателен.',
            'locale.string'             => 'Язык должен быть строкой.',
            'locale.size'               => 'Код языка должен состоять из 2 символов (например, "ru", "en", "kz").',
            'locale.in'                 => 'Допустимые языки: ru, en, kz.',

            'title.required'            => 'Название видео обязательно для заполнения.',
            'title.string'              => 'Название видео должно быть строкой.',
            'title.max'                 => 'Название видео не должно превышать 255 символов.',
            'title.unique'              => 'Видео с таким названием уже существует.',

            'url.required'              => 'URL видео обязателен.',
            'url.string'                => 'URL видео должен быть строкой.',
            'url.max'                   => 'URL видео не должен превышать 255 символов.',
            'url.unique'                => 'Видео с таким URL уже существует.',

            'short.string'              => 'Краткое описание должно быть строкой.',
            'short.max'                 => 'Краткое описание не должно превышать 255 символов.',

            'description.string'        => 'Описание должно быть строкой.',

            'author.string'             => 'Имя автора должно быть строкой.',
            'author.max'                => 'Имя автора не должно превышать 255 символов.',

            'views.integer'             => 'Количество просмотров должно быть числом.',
            'views.min'                 => 'Количество просмотров не может быть отрицательным.',

            'likes.integer'             => 'Количество лайков должно быть числом.',
            'likes.min'                 => 'Количество лайков не может быть отрицательным.',

            'meta_title.max'            => 'Meta заголовок не должен превышать 255 символов.',
            'meta_keywords.max'         => 'Meta ключевые слова не должны превышать 255 символов.',
            'meta_desc.max'             => 'Meta описание не должно превышать 255 символов.',

            'sort.integer'              => 'Поле сортировки должно быть числом.',
            'activity.required'         => 'Поле активности обязательно для заполнения.',
            'activity.boolean'          => 'Поле активности должно быть логическим значением.',

            'left.required'             => 'Поле видео в левой колонке обязательно для заполнения.',
            'left.boolean'              => 'Поле видео в левой колонке должно быть логическим значением.',

            'main.required'             => 'Поле главная видео обязательно для заполнения.',
            'main.boolean'              => 'Поле главная видео должно быть логическим значением.',

            'right.required'            => 'Поле видео в правой колонке обязательно для заполнения.',
            'right.boolean'             => 'Поле видео в правой колонке должно быть логическим значением.',

            'sections.array'            => 'Секции должны быть массивом.',
            'articles.array'            => 'Теги должны быть массивом.',
            'related_videos.array'      => 'Список связанных статей должен быть массивом.',

            'images.array'              => 'Изображения должны быть массивом.',
            'images.*.id.exists'        => 'Указанного изображения не существует.',
            'images.*.file.image'       => 'Файл должен быть изображением.',
            'images.*.file.mimes'       => 'Файл должен быть формата jpeg, jpg, png или webp.',
            'images.*.file.max'         => 'Размер файла изображения не должен превышать 10 Мб.',
        ];
    }
}
