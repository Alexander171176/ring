<?php

namespace App\Http\Resources\Admin\Video;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VideoImageResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // Получаем первое медиа из коллекции 'images'
        $media = $this->getFirstMedia('images');

        return [
            'id'         => $this->id,
            'order'      => $this->order,
            'alt'        => $this->alt,
            'caption'    => $this->caption,
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),

            // URL оригинального изображения
            'url' => $media ? $media->getUrl() : null,

            // URL WebP-версии изображения
            'webp_url' => $media ? $media->getUrl('webp') : null,

            // Дополнительные свойства (опционально)
            'mime_type' => $media->mime_type ?? null,
            'size'      => $media->size ?? null,
        ];
    }
}
