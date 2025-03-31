<?php

namespace App\Http\Resources\Admin\Tag;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TagResource extends JsonResource
{
    /**
     * Преобразует ресурс в массив.
     */
    public function toArray(Request $request): array
    {
        return [
            'id'            => $this->id,
            'locale'        => $this->locale,
            'name'          => $this->name,
            'slug'          => $this->slug,
            'short'         => $this->short,
            'description'   => $this->description,
            'meta_title'    => $this->meta_title,
            'meta_keywords' => $this->meta_keywords,
            'meta_desc'     => $this->meta_desc,
            'created_at'    => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at'    => $this->updated_at?->format('Y-m-d H:i:s'),
        ];
    }
}
