<?php

namespace App\Http\Resources\Admin\Rubric;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RubricTranslationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'locale' => $this->locale,
            'title' => $this->title,
            'url' => $this->url,
            'short' => $this->short,
            'description' => $this->description,
            'meta_title' => $this->meta_title,
            'meta_keywords' => $this->meta_keywords,
            'meta_desc' => $this->meta_desc,
        ];
    }
}
