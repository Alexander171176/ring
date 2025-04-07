<?php

namespace App\Http\Resources\Admin\Banner;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BannerSharedResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     * (Облегченная версия)
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // Получаем первое изображение (если было загружено)
        $firstImage = $this->whenLoaded('images', fn() => $this->resource->images->first());

        return [
            'id'            => $this->id,
            'title'         => $this->title,
            'activity'      => $this->activity, // boolean
            'link'          => $this->link,     // Ссылка может быть важна
            // 'locale'     => $this->locale,  // Добавить, если мультиязычные

            // URL первого превью
            'thumbnail_url' => $firstImage ? $firstImage->thumb_url : null,
        ];
    }
}
