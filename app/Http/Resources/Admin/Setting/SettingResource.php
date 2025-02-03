<?php

namespace App\Http\Resources\Admin\Setting;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SettingResource extends JsonResource
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
            'type' => $this->type,
            'option' => $this->option,
            'value' => $this->value,
            'constant' => $this->constant,
            'category' => $this->category,
            'description' => $this->description,
            'activity' => $this->activity,
        ];
    }
}
