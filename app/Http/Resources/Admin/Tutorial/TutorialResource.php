<?php

namespace App\Http\Resources\Admin\Tutorial;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TutorialResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'sort' => $this->sort,
            'activity' => $this->activity,
            'icon' => $this->icon,
            'title' => $this->title,
            'url' => $this->url,
            'short' => $this->short,
            'description' => $this->description,
            'views' => $this->views,
            'image_url' => $this->image_url,
            'seo_title' => $this->seo_title,
            'seo_alt' => $this->seo_alt,
            'meta_title' => $this->meta_title,
            'meta_keywords' => $this->meta_keywords,
            'meta_desc' => $this->meta_desc,
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at->format('Y-m-d H:i:s'),
            'guides_count' => $this->guides_count, // Используем количество гайдов
        ];
    }
}
