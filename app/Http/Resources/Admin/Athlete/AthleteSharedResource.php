<?php

namespace App\Http\Resources\Admin\Athlete;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AthleteSharedResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        if (is_null($this->resource)) {
            return [];
        }

        return [
            'id' => $this->id,
            'full_name' => $this->full_name, // Аксессор
            'nickname' => $this->nickname,
            'activity' => (bool) $this->activity,
            // Можно добавить URL на превью аватара, если он нужен в списках
            // 'avatar_thumb' => $this->whenLoaded('images', function() {
            //    $firstImage = $this->images->first();
            //    return $firstImage ? $firstImage->thumb_url : ($this->avatar ? asset('storage/' . $this->avatar) : null);
            // }),
            // Или если у Athlete есть своя Spatie коллекция для аватара:
            // 'avatar_thumb' => $this->getFirstMediaUrl('profile_avatar', 'thumb'),

            // Если простой аватар из поля 'avatar' и нужно его превью (требует доп. логики генерации превью для простого файла)
            // 'avatar_url' => $this->avatar ? asset('storage/' . $this->avatar) : null,
        ];
    }
}
