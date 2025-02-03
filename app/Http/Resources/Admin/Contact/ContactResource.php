<?php

namespace App\Http\Resources\Admin\Contact;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ContactResource extends JsonResource
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
            'image' => $this->image,
            'tailwind' => $this->tailwind,
            'title' => $this->title,
            'content' => $this->content,
        ];
    }
}
