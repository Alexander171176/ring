<?php

namespace App\Http\Resources\Admin\About;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SectionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  Request  $request
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'tailwind' => $this->tailwind,
            'image' => $this->image,
            'title' => $this->title,
            'content' => $this->content,
            'sort' => $this->sort,
            'activity' => $this->activity,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
