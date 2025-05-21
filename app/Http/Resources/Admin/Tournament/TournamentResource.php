<?php

namespace App\Http\Resources\Admin\Tournament;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TournamentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'sort' => $this->sort,
            'activity' => $this->activity,
            'locale' => $this->locale,
            'type' => $this->type,
            'name' => $this->name,
            'tournament_date_time' => $this->tournament_date_time,
            'status' => $this->status,
            'venue' => $this->venue,
            'city' => $this->city,
            'country' => $this->country,
            'short' => $this->short,
            'description' => $this->description,
            'weight_class_name' => $this->weight_class_name,
            'rounds_scheduled' => $this->rounds_scheduled,
            'is_title_fight' => $this->is_title_fight,
            'method_of_victory' => $this->method_of_victory,
            'round_of_finish' => $this->round_of_finish,
            'time_of_finish' => $this->time_of_finish,
            'details' => $this->details,
            'is_main_card_event' => $this->is_main_card_event,

            'parent_tournament_id' => $this->parent_tournament_id,
            'winner_id' => $this->winner_id,

            // Связи
            'winner' => $this->whenLoaded('winner'),
            'athletes' => $this->whenLoaded('athletes'),
            'parentTournament' => $this->whenLoaded('parentTournament'),
            'childTournaments' => $this->whenLoaded('childTournaments'),

            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
