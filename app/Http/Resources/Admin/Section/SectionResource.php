<?php

namespace App\Http\Resources\Admin\Section;

use App\Http\Resources\Admin\Article\ArticleResource;
use App\Http\Resources\Admin\Rubric\RubricResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SectionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
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
            'short'         => $this->short,

            // Связанные рубрики и статьи
            'rubrics' => RubricResource::collection($this->whenLoaded('rubrics')),
            'articles'    => ArticleResource::collection($this->whenLoaded('articles')),

            // Подсчитываем активные статьи по всем секциям
            'active_articles_count' => $this->whenLoaded('sections', function () {
                return $this->sections->reduce(function ($carry, $section) {
                    return $carry + ($section->articles ? $section->articles->count() : 0);
                }, 0);
            }),
        ];
    }
}
