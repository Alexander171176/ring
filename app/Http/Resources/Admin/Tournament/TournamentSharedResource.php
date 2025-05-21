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
            'locale' => $this->locale,
            'name' => $this->name,
            'type' => $this->type,
            'status' => $this->status,
            'date' => $this->tournament_date_time,
            'is_main_card_event' => $this->is_main_card_event,
            'activity' => $this->activity,
        ];
    }
}
