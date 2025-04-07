<?php

namespace App\Http\Resources\Admin\Article;

// use App\Http\Resources\Admin\Section\SectionResource; // Не нужен здесь
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ArticleSharedResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     * (Облегченная версия для списков)
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // Получаем первое изображение (если оно было загружено с with('images'))
        // или null, если не было загружено
        $firstImage = $this->whenLoaded('images', fn() => $this->resource->images->first());

        return [
            'id'            => $this->id,
            'locale'        => $this->locale,
            'title'         => $this->title,
            'url'           => $this->url,
            'activity'      => $this->activity, // boolean
            'published_at'  => $this->published_at?->toDateString(), // Пример формата YYYY-MM-DD
            // Можно добавить URL первого изображения (превью)
            'thumbnail_url' => $firstImage ? $firstImage->thumb_url : null, // Используем аксессор из ArticleImageResource

            // 'sort'          => $this->sort, // Возможно, сортировка не нужна в shared
            // 'short'         => $this->short, // Возможно, краткое описание не нужно
        ];
    }
}
