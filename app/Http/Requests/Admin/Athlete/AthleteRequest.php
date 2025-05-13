<?php

namespace App\Http\Requests\Admin\Athlete;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\Admin\Athlete\AthleteImage; // Для Rule::exists

class AthleteRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // TODO: Implement authorization logic if needed
        // For example: return $this->user()->can('manage_athletes');
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $athleteId = $this->route('athlete') ? $this->route('athlete')->id : null;

        return [
            'sort'                          => ['sometimes', 'nullable', 'integer', 'min:0'],
            'activity'                      => ['sometimes', 'boolean'],
            'first_name'                    => ['required', 'string', 'max:255'],
            'last_name'                     => ['required', 'string', 'max:255'],
            'nickname'                      => ['nullable', 'string', 'max:255'],
            'date_of_birth'                 => ['nullable', 'date_format:Y-m-d', 'before_or_equal:today'],
            'nationality'                   => ['nullable', 'string', 'max:255'],
            'height_cm'                     => ['nullable', 'integer', 'min:50', 'max:300'], // Реалистичные пределы
            'reach_cm'                      => ['nullable', 'integer', 'min:50', 'max:350'], // Реалистичные пределы
            'stance'                        => ['nullable', 'string', Rule::in(['orthodox', 'southpaw', 'switch'])],
            'bio'                           => ['nullable', 'string', 'max:65535'], // TEXT обычно до 64KB
            'short'                         => ['nullable', 'string', 'max:1000'], // Увеличил, если 255 мало для string
            'description'                   => ['nullable', 'string', 'max:65535'],

            // Простое поле аватара (если используется для URL или пути к файлу, НЕ для загрузки файла)
            // Если это URL, который вы вводите вручную:
            // 'avatar'                     => ['nullable', 'string', 'url', 'max:2048'],
            // Если это путь к файлу, который уже есть на сервере (выбирается из списка или что-то подобное):
            'avatar'                        => ['nullable', 'string', 'max:2048'],
            // Если вы все же хотите разрешить ЗАГРУЗКУ файла через это поле (менее вероятно при вашей структуре):
            // 'avatar_upload'              => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'], // 2MB

            // Статистика (обычно не редактируется напрямую)
            'wins'                          => ['sometimes', 'integer', 'min:0'],
            'losses'                        => ['sometimes', 'integer', 'min:0'],
            'draws'                         => ['sometimes', 'integer', 'min:0'],
            'no_contests'                   => ['sometimes', 'integer', 'min:0'],
            'wins_by_ko'                    => ['sometimes', 'integer', 'min:0'],
            'wins_by_submission'            => ['sometimes', 'integer', 'min:0'],
            'wins_by_decision'              => ['sometimes', 'integer', 'min:0'],

            // Управление связанными изображениями (AthleteImage)
            // Этот блок предполагает, что вы передаете массив объектов `images`
            // Каждый объект может описывать существующее или новое изображение
            'images'                        => ['nullable', 'array'],
            'images.*.id'                   => [
                'nullable', // Может быть новым изображением без ID
                'integer',
                Rule::exists(AthleteImage::class, 'id')->where(function ($query) {
                    // Дополнительное условие, если нужно, например, проверять, что AthleteImage не привязан к другому Athlete
                    // (хотя ваша структура M:M это позволяет)
                })
            ],
            'images.*.alt'                  => ['nullable', 'string', 'max:255'],
            'images.*.caption'              => ['nullable', 'string', 'max:1000'],
            'images.*.order'                => [ // Порядок картинки для атлета (в пивотной таблице)
                'required_with:images.*.id', // Если есть ID, должен быть и порядок
                'nullable', // Если это новая картинка, порядок может быть не задан или задан в контроллере
                'integer',
                'min:0'
            ],
            'images.*.file'                 => [ // Для загрузки нового файла для AthleteImage
                Rule::requiredIf(function () {
                    // Требуется, если нет ID (т.е. создается новый AthleteImage)
                    $imageData = request()->input(str_replace('.file', '', $this->currentPath));
                    return empty($imageData['id']);
                }),
                'nullable', // Может быть обновлением существующего AthleteImage без смены файла
                'file', // Laravel сам определит, что это файл
                'image', // Должен быть изображением
                'mimes:jpeg,jpg,png,webp,gif', // Допустимые типы
                'max:5120' // Максимальный размер файла (5MB)
            ],
            'images.*._delete'              => ['sometimes', 'boolean'], // Флаг для удаления связи или самого AthleteImage
            'images_order'                  => ['nullable', 'array'], // Альтернативный способ передать порядок всех изображений
            'images_order.*'                => ['integer', Rule::exists(AthleteImage::class, 'id')],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages(): array
    {
        $messages = [];
        // Общие сообщения
        $messages['required'] = 'Поле обязательно для заполнения.'; // Общее для всех :attribute.required
        $messages['string'] = 'Поле должно быть строкой.';
        $messages['integer'] = 'Поле должно быть целым числом.';
        $messages['boolean'] = 'Поле должно иметь значение истина или ложь.';
        $messages['date_format'] = 'Неверный формат даты. Ожидается ГГГГ-ММ-ДД.';
        $messages['before_or_equal'] = 'Дата не может быть позже сегодняшнего дня.';
        $messages['min'] = 'Значение поля должно быть не менее :min.';
        $messages['max'] = 'Значение поля не должно превышать :max (для строк/массивов - символов/элементов, для чисел - значение).';
        $messages['url'] = 'Поле должно быть корректным URL.';
        $messages['image'] = 'Файл должен быть изображением.';
        $messages['mimes'] = 'Файл должен быть одного из типов: :values.';
        $messages['file'] = 'Некорректный файл.';
        $messages['array'] = 'Поле должно быть массивом.';
        $messages['exists'] = 'Выбранное значение не существует.';

        // Сообщения для конкретных полей
        $messages['first_name.required'] = 'Имя спортсмена обязательно для заполнения.';
        $messages['first_name.max'] = 'Имя спортсмена не должно превышать 255 символов.';
        $messages['last_name.required'] = 'Фамилия спортсмена обязательна для заполнения.';
        $messages['last_name.max'] = 'Фамилия спортсмена не должна превышать 255 символов.';
        $messages['nickname.max'] = 'Прозвище не должно превышать 255 символов.';
        $messages['nationality.max'] = 'Национальность не должна превышать 255 символов.';
        $messages['height_cm.min'] = 'Рост не может быть менее 50 см.';
        $messages['height_cm.max'] = 'Рост не может быть более 300 см.';
        $messages['reach_cm.min'] = 'Размах рук не может быть менее 50 см.';
        $messages['reach_cm.max'] = 'Размах рук не может быть более 350 см.';
        $messages['stance.in'] = 'Недопустимое значение для стойки.';
        $messages['bio.max'] = 'Биография слишком длинная.';
        $messages['short.max'] = 'Краткое описание не должно превышать 1000 символов.';
        $messages['description.max'] = 'Полное описание слишком длинное.';
        $messages['avatar.max'] = 'Путь к аватару слишком длинный (макс. 2048 символов).';
        // $messages['avatar_upload.max'] = 'Размер файла аватара не должен превышать 2MB.';

        // Сообщения для массива images
        // Используем плейсхолдер :attribute, который Laravel заменит на images.index.field
        // или вы можете определить их более точно ниже
        $messages['images.*.id.integer'] = 'ID изображения (в images) должен быть числом.';
        $messages['images.*.id.exists'] = 'Изображение с таким ID (в images) не найдено.';
        $messages['images.*.alt.max'] = 'Alt текст для изображения (в images) не должен превышать 255 символов.';
        $messages['images.*.caption.max'] = 'Подпись для изображения (в images) не должна превышать 1000 символов.';
        $messages['images.*.order.required_with'] = 'Порядок для существующего изображения (в images) обязателен.';
        $messages['images.*.order.integer'] = 'Порядок изображения (в images) должен быть числом.';
        $messages['images.*.order.min'] = 'Порядок изображения (в images) не может быть отрицательным.';
        $messages['images.*.file.required'] = 'Файл для нового изображения (в images) обязателен.';
        $messages['images.*.file.file'] = 'Некорректный файл изображения (в images).';
        $messages['images.*.file.image'] = 'Файл изображения (в images) должен быть картинкой.';
        $messages['images.*.file.mimes'] = 'Допустимые типы для файла изображения (в images): :values.';
        $messages['images.*.file.max'] = 'Размер файла изображения (в images) не должен превышать 5MB.';
        $messages['images.*._delete.boolean'] = 'Флаг удаления изображения (в images) должен быть true или false.';
        $messages['images_order.array'] = 'Порядок изображений должен быть массивом ID.';
        $messages['images_order.*.integer'] = 'Каждый ID в порядке изображений должен быть числом.';
        $messages['images_order.*.exists'] = 'ID изображения в списке порядка не найден.';


        // Если хотите более конкретные сообщения для каждого элемента массива images:
        // foreach ($this->input('images', []) as $key => $value) {
        //     $messages["images.{$key}.id.integer"] = "ID изображения #".($key+1)." должен быть числом.";
        //     $messages["images.{$key}.file.required"] = "Файл для нового изображения #".($key+1)." обязателен.";
        // }

        return $messages;
    }

    /**
     * Prepare the data for validation.
     *
     * @return void
     */
    protected function prepareForValidation(): void
    {
        // Преобразование 'activity' в boolean, если оно пришло как строка 'true'/'false' или 0/1
        if ($this->has('activity')) {
            $this->merge([
                'activity' => filter_var($this->input('activity'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE),
            ]);
        }
        // Аналогично для images.*._delete
        if ($this->has('images')) {
            $images = $this->input('images');
            foreach ($images as $key => $image) {
                if (isset($image['_delete'])) {
                    $images[$key]['_delete'] = filter_var($image['_delete'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
                }
            }
            $this->merge(['images' => $images]);
        }
    }
}
