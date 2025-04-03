<?php

namespace App\Http\Resources\Admin\Banner;

use App\Http\Resources\Admin\Article\ArticleImageResource;
use App\Http\Resources\Admin\Section\SectionResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BannerResource extends JsonResource
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
            'left'          => $this->left,
            'right'         => $this->right,
            'title'         => $this->title,
            'link'          => $this->link,
            'short'         => $this->short,
            'comment'       => $this->comment,
            'created_at'    => $this->created_at?->format('d-m-Y'),
            'updated_at'    => $this->updated_at?->format('Y-m-d H:i:s'),

            // Связанные рубрики
            'sections' => SectionResource::collection($this->whenLoaded('sections')),

            // Связанные изображения
            'images' => ArticleImageResource::collection($this->whenLoaded('images')),
        ];
    }
}
