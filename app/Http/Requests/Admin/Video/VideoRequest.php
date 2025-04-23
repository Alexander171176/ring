<?php

namespace App\Http\Requests\Admin\Video;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Http\UploadedFile; // Для проверки типа файла

class VideoRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // TODO: Заменить на реальную проверку прав доступа
        // if ($this->isMethod('POST')) return $this->user()->can('create videos');
        // if ($this->isMethod('PUT') || $this->isMethod('PATCH')) return $this->user()->can('update', $this->route('video'));
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $isCreating = $this->isMethod('post');
        $videoId    = $this->route('video')?->id;

        // условное правило для запрета связывать видео само с собой
        $relatedExistsRule = Rule::exists('videos', 'id')->where(function($query) use ($videoId) {
            if ($videoId) {
                $query->where('id', '!=', $videoId);
            }
        });

        return [
            'sort'            => 'nullable|integer|min:0',
            'activity'        => 'required|boolean',
            'left'            => 'required|boolean',
            'main'            => 'required|boolean',
            'right'           => 'required|boolean',
            'locale'          => ['required', 'string', 'size:2', Rule::in(['ru','en','kz'])],
            'title'           => [
                'required','string','max:255',
                Rule::unique('videos')->where(fn($q)=>$q->where('locale',$this->input('locale')))->ignore($videoId),
            ],
            'url'             => [
                'required','string','max:500','regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
                Rule::unique('videos')->where(fn($q)=>$q->where('locale',$this->input('locale')))->ignore($videoId),
            ],
            'short'           => 'nullable|string|max:255',
            'description'     => 'nullable|string',
            'author'          => 'nullable|string|max:255',
            'published_at'    => 'nullable|date_format:Y-m-d',
            'duration'        => 'nullable|integer|min:0',
            'source_type'       => ['required', Rule::in(['local','youtube','vimeo','code'])],

            // для кодового варианта вводим отдельное поле embed_code:
            'embed_code' => [
                Rule::requiredIf(fn() => $this->input('source_type') === 'code'),
                'nullable','string','max:65535', // количество символов кода HTML
            ],

            // Для YouTube/Vimeo
            'external_video_id' => [
                'nullable',
                Rule::requiredIf(fn() => in_array($this->input('source_type'), ['youtube','vimeo'])),
                'string',
                'max:65535',
            ],

            // для локального: требуем либо файл, либо URL
            'video_file' => [
                'nullable',
                Rule::requiredIf(fn() => $this->input('source_type') === 'local' && $isCreating),
                'file',
                'mimes:mp4,mov,ogg,qt,webm,avi,mpeg,wmv',
                'max:204800', // размер видеофайла
            ],
            // вот это поле — пока просто nullable
            'video_url'       => ['nullable', 'string', 'max:500'], // количество символов url

            'views'           => 'nullable|integer|min:0',
            'likes'           => 'nullable|integer|min:0',
            'meta_title'      => 'nullable|string|max:255',
            'meta_keywords'   => 'nullable|string|max:255',
            'meta_desc'       => 'nullable|string',

            'sections'        => ['nullable','array'],
            'sections.*.id'   => ['required_with:sections','integer','exists:sections,id'],

            'articles'        => ['nullable','array'],
            'articles.*.id'   => ['required_with:articles','integer','exists:articles,id'],

            'related_videos'     => ['nullable','array'],
            'related_videos.*.id' => ['required_with:related_videos','integer',$relatedExistsRule],

            'images'          => ['nullable','array'],
            'images.*.id'     => [
                'nullable','integer',
                Rule::exists('video_images','id'),
                Rule::prohibitedIf(fn() => $isCreating),
            ],
            'images.*.order'   => ['nullable','integer','min:0'],
            'images.*.alt'     => ['nullable','string','max:255'],
            'images.*.caption' => ['nullable','string','max:255'],
            'images.*.file'    => [
                'nullable','file','image',
                'mimes:jpeg,jpg,png,gif,svg,webp','max:10240',
                'required_without:images.*.id',
            ],

            'deletedImages'     => ['sometimes','array'],
            'deletedImages.*'   => ['integer','exists:video_images,id'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        // Наследуем стандартные + добавляем/переопределяем свои
        return array_merge(parent::messages(), [
            'locale.required'           => 'Язык видео обязателен.',
            'locale.size'               => 'Код языка должен состоять из :size символов.',
            'locale.in'                 => 'Допустимые языки: :values.',

            'title.required'            => 'Название видео обязательно.',
            'title.max'                 => 'Название видео не должно превышать :max символов.',
            'title.unique'              => 'Видео с таким Названием и Языком уже существует.',

            'url.required'              => 'URL видео обязателен.',
            'url.max'                   => 'URL видео не должен превышать :max символов.',
            'url.regex'                 => 'URL должен содержать только латинские буквы, цифры и дефисы.',
            'url.unique'                => 'Видео с таким URL и Языком уже существует.',

            'published_at.date_format'  => 'Некорректный формат даты публикации (ожидается ГГГГ-ММ-ДД).',

            'duration.integer'          => 'Длительность видео должна быть целым числом (секунды).',
            'duration.min'              => 'Длительность видео не может быть отрицательной.',

            'source_type.required'      => 'Необходимо выбрать тип источника видео.',
            'source_type.in'            => 'Выбран недопустимый тип источника видео.',

            'external_video_id.required' => 'Нужно указать ссылку/ID только для YouTube или Vimeo.',
            'external_video_id.max'      => 'Поле ID/ссылки/кода слишком длинное (макс. :max символов).',

            'video_file.required'       => 'Необходимо загрузить файл для локального видео.',
            'video_file.file'           => 'Проблема при загрузке видеофайла.',
            'video_file.mimes'          => 'Недопустимый формат видеофайла. Разрешены: :values.',
            'video_file.max'            => 'Видеофайл слишком большой (макс. :max Кб).',

            'video_url.required'        => 'Нужно указать URL для локального видео или вставить код для типа «code».',

            'short.max'                 => 'Краткое описание не должно превышать :max символов.',
            'description.max'           => 'Описание слишком длинное (макс. :max символов).', // Добавлено, если нужно ограничение

            'author.max'                => 'Имя автора не должно превышать :max символов.',

            'views.min'                 => 'Количество просмотров не может быть отрицательным.',
            'likes.min'                 => 'Количество лайков не может быть отрицательным.',

            'meta_title.max'            => 'Meta заголовок не должен превышать :max символов.',
            'meta_keywords.max'         => 'Meta ключевые слова не должны превышать :max символов.',
            'meta_desc.max'             => 'Meta описание слишком длинное (макс. :max символов).', // Убрано

            'sort.min'                  => 'Поле сортировки не может быть отрицательным.',
            'activity.required'         => 'Поле активности обязательно.',
            'left.required'             => 'Поле "В левой колонке" обязательно.',
            'main.required'             => 'Поле "Главное видео" обязательно.',
            'right.required'            => 'Поле "В правой колонке" обязательно.',

            'sections.*.id.required_with' => 'ID секции обязателен.',
            'sections.*.id.exists'      => 'Выбрана несуществующая секция (ID: :value).', // :value покажет ID
            'articles.*.id.required_with' => 'ID статьи обязателен.',
            'articles.*.id.exists'      => 'Выбрана несуществующая статья (ID: :value).',

            'related_videos.*.id.required_with' => 'ID связанного видео обязателен.',
            'related_videos.*.id.exists'      => 'Выбрано несуществующее связанное видео (ID: :value).',
            'related_videos.*.id.where_not'   => 'Видео не может быть связано само с собой.', // Хотя whereNot не создает сообщение

            'images.*.id.exists'        => 'Указанного изображения превью не существует (ID: :value).',
            'images.*.id.prohibited'    => 'ID изображения превью нельзя передавать при создании.',
            'images.*.order.min'        => 'Порядок изображения превью не может быть отрицательным.',
            'images.*.file.required'    => 'Файл изображения превью обязателен для новых изображений.',
            'images.*.file.image'       => 'Файл превью должен быть изображением.',
            'images.*.file.mimes'       => 'Недопустимый формат файла превью. Разрешены: :values.',
            'images.*.file.max'         => 'Файл превью слишком большой (макс. :max Кб).',
            'images.*.file.required_without' => 'Файл изображения обязателен для новых изображений.',

            'deletedImages.*.exists'    => 'Попытка удалить несуществующее изображение превью (ID: :value).',
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
            'left' => filter_var($this->input('left'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? false,
            'main' => filter_var($this->input('main'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? false,
            'right' => filter_var($this->input('right'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? false,
        ]);

        $this->merge([
            'views' => is_numeric($this->input('views')) ? (int)$this->input('views') : 0,
            'likes' => is_numeric($this->input('likes')) ? (int)$this->input('likes') : 0,
        ]);

        parent::prepareForValidation();
        // Очистим поля в зависимости от source_type
        if ($this->input('source_type') !== 'code') {
            $this->merge(['embed_code' => null]);
        }
        if (! in_array($this->input('source_type'), ['youtube','vimeo'])) {
            $this->merge(['external_video_id' => null]);
        }
        if ($this->input('source_type') !== 'local') {
            $this->merge(['video_file' => null, 'video_url' => null]);
        }
    }
}
