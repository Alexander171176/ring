<?php

namespace App\Http\Resources\Admin\Athlete;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AthleteSharedResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        if (is_null($this->resource)) {
            return [];
        }

        return [
            'id' => $this->id,
            'locale' => $this->locale,
            'full_name' => $this->full_name, // Аксессор
            'nickname' => $this->nickname,
            'activity' => (bool) $this->activity,
            'avatar' => $this->avatar,
        ];
    }
}
