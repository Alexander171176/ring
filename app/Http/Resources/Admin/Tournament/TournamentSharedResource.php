<?php

namespace App\Http\Resources\Admin\Tournament;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TournamentSharedResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'tournament_date_time' => $this->tournament_date_time?->format('Y-m-d'),
            'status' => $this->status,
            'locale' => $this->locale,
            'is_title_fight' => $this->is_title_fight,

            'fighter_red' => $this->whenLoaded('fighterRed', fn () => [
                'id' => $this->fighterRed->id,
                'full_name' => $this->fighterRed->full_name,
            ]),

            'fighter_blue' => $this->whenLoaded('fighterBlue', fn () => [
                'id' => $this->fighterBlue->id,
                'full_name' => $this->fighterBlue->full_name,
            ]),

            'winner' => $this->whenLoaded('winner', fn () => [
                'id' => $this->winner->id,
                'full_name' => $this->winner->full_name,
            ]),
        ];
    }
}
