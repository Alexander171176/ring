<?php

namespace App\Http\Resources\Admin\Article;

use App\Http\Resources\Admin\Section\SectionResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ArticleResource extends JsonResource
{
    /**
     * Преобразует ресурс в массив.
     */
    public function toArray(Request $request): array
    {
        return [
            'id'            => $this->id,
            'sort'          => $this->sort,
            'activity'      => $this->activity,
            'main'          => $this->main,
            'sidebar'       => $this->sidebar,
            'locale'        => $this->locale,
            'title'         => $this->title,
            'url'           => $this->url,
            'short'         => $this->short,
            'description'   => $this->description,
            'author'        => $this->author,
            'views'         => $this->views,
            'likes'         => $this->likes,
            'meta_title'    => $this->meta_title,
            'meta_keywords' => $this->meta_keywords,
            'meta_desc'     => $this->meta_desc,
            'created_at' => $this->created_at?->format('d-m-Y'),
            'updated_at'    => $this->updated_at?->format('Y-m-d H:i:s'),

            // Количество комментариев
            'comments_count' => $this->whenNotNull($this->comments_count, 0),

            // Связанные рубрики
            'sections' => SectionResource::collection($this->whenLoaded('sections')),

            // Связанные теги
            'tags' => TagResource::collection($this->whenLoaded('tags')),

            // Связанные изображения
            'images' => ArticleImageResource::collection($this->whenLoaded('images') ?? collect()),

            // Связанные статьи (ручные рекомендации)
            'related_articles' => ArticleResource::collection($this->whenLoaded('relatedArticles')),

        ];
    }
}
