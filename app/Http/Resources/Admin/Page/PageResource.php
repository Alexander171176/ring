<?php

namespace App\Http\Resources\Admin\Page;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PageResource extends JsonResource
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
            'title' => $this->title,
            'url' => $this->url,
            'slug' => $this->slug,
            'content' => $this->content,
            'meta_title' => $this->meta_title,
            'meta_keywords' => $this->meta_keywords,
            'meta_desc' => $this->meta_desc,
            'activity' => $this->activity,
            'print_in_menu' => $this->print_in_menu,
            'without_style' => $this->without_style,
            'parent_id' => $this->parent_id,
            'created_at' => $this->created_at ? $this->created_at->format('Y-m-d H:i:s') : null, // Проверка на null
            'updated_at' => $this->updated_at ? $this->updated_at->format('Y-m-d H:i:s') : null, // Аналогичная проверка для updated_at
        ];
    }
}
