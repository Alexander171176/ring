<?php

namespace App\Http\Resources\Admin\Section;

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

            // Связанные рубрики
            'rubrics' => RubricResource::collection($this->whenLoaded('rubrics')),

            // Количество статей, если есть
            'articles_count' => $this->whenLoaded('articles', fn() => $this->articles->count()),
        ];
    }
}
