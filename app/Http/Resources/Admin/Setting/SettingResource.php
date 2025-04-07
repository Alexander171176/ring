<?php

namespace App\Http\Resources\Admin\Setting;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SettingResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'option' => $this->option,
            // Поле 'value' уже будет декодировано в PHP массив/объект благодаря $casts['value' => 'json'] в модели.
            // Laravel Resource автоматически правильно сериализует его обратно в JSON в ответе.
            'value' => $this->value,
            'constant' => $this->constant,
            'category' => $this->category,
            'description' => $this->description,
            'activity' => $this->activity, // Уже boolean благодаря $casts['activity' => 'boolean']
            // Даты скрыты через $hidden в модели, поэтому не добавляем их сюда,
            // если только они не нужны явно для каких-то целей.
            // 'created_at'    => $this->created_at?->toIso8601String(),
            // 'updated_at'    => $this->updated_at?->toIso8601String(),
        ];
    }
}
