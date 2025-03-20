<?php

namespace App\Http\Resources\Admin\Rubric;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RubricResource extends JsonResource
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
            'icon'          => $this->icon,
            'locale'        => $this->locale,
            'title'         => $this->title,
            'url'           => $this->url,
            'short'         => $this->short,
            'meta_title'    => $this->meta_title,
            'meta_keywords' => $this->meta_keywords,
            'meta_desc'     => $this->meta_desc,
            'created_at'    => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at'    => $this->updated_at?->format('Y-m-d H:i:s'),
            // Количество секций, если есть
            'sections_count' => $this->whenLoaded('sections', fn() => $this->sections->count()),
            // Количество статей, если есть
            'articles_count' => $this->whenLoaded('articles', fn() => $this->articles->count()),
        ];
    }
}
