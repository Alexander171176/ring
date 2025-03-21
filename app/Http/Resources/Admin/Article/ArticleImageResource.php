<?php

namespace App\Http\Resources\Admin\Article;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ArticleImageResource extends JsonResource
{
    /**
     * Преобразует ресурс в массив.
     */
    public function toArray(Request $request): array
    {
        return [
            'id'      => $this->id,
            'order'   => $this->order,
            'path'    => $this->path,
            'url'        => asset('storage/' . $this->path), // полный URL изображения
            'alt'     => $this->alt,
            'caption' => $this->caption,
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
        ];
    }
}
