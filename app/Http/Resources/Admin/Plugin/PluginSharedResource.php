<?php

namespace App\Http\Resources\Admin\Plugin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PluginSharedResource extends JsonResource
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
            'icon' => $this->icon,
            'name' => $this->name,
            'version' => $this->version,
            'code' => $this->code,
            'options' => $this->options,
            'description' => $this->description,
            'readme' => $this->readme,
            'templates' => $this->templates,
            'activity' => $this->activity,
        ];
    }
}
