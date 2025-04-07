<?php

namespace App\Http\Resources\Admin\Video;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
// Можно импортировать VideoImageResource, если нужно первое превью
// use App\Http\Resources\Admin\Video\VideoImageResource;

class VideoSharedResource extends JsonResource
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
            'locale'        => $this->locale,
            'title'         => $this->title,
            'url'           => $this->url,
            'activity'      => $this->activity, // boolean
            'source_type'   => $this->source_type,
            'published_at'  => $this->published_at?->toDateString(), // YYYY-MM-DD

            // URL или код в зависимости от типа
            'display_source' => match ($this->source_type) {
                'local' => $this->video_url, // Аксессор из модели
                'youtube', 'vimeo' => $this->embed_url, // Аксессор из модели
                'code' => $this->video_code, // Аксессор из модели
                default => null,
            },

            // Можно добавить URL первого превью
            'thumbnail_url' => $firstImage ? $firstImage->thumb_url : null, // Используем аксессор из VideoImage

            // 'sort'       => $this->sort,
            // 'short'      => $this->short,
        ];
    }
}
