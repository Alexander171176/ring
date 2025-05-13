<?php

namespace App\Http\Resources\Admin\Athlete;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AthleteImageResource extends JsonResource
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
            'order' => $this->order, // Порядок самого объекта AthleteImage
            'alt' => $this->alt,
            'caption' => $this->caption,
            'image_url' => $this->image_url, // Аксессор getImageUrlAttribute()
            'webp_responsive_url' => $this->webp_responsive_url, // Аксессор
            // Если вы хотите передавать готовый HTML тег:
            // 'webp_responsive_image_tag' => $this->webp_responsive_image_tag,
            // Если вы хотите передавать srcset для Vue:
            // 'webp_srcset' => $this->whenLoaded('media', fn() => $this->getFirstMedia('images')?->getSrcset('webp_responsive')),
            'thumb_url' => $this->thumb_url, // Аксессор
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),

            // Информация о пивотной таблице, если ресурс используется в контексте Athlete
            // 'pivot_order' будет доступно, если вы загрузили связь с withPivot('order')
            // и используете этот ресурс при выводе Athlete->images
            'pivot_order' => $this->whenPivotLoaded('athlete_has_images', function () {
                return $this->pivot->order;
            }),
        ];
    }
}
