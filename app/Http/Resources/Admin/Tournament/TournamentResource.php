<?php

namespace App\Http\Resources\Admin\Tournament;

use App\Http\Resources\Admin\Video\VideoResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class TournamentResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'sort' => $this->sort,
            'activity' => $this->activity,
            'left' => $this->left,          // boolean
            'main' => $this->main,          // boolean
            'right' => $this->right,         // boolean
            'locale' => $this->locale,
            'name' => $this->name,
            'url' => $this->url,
            'short' => $this->short,
            'description' => $this->description,
            'tournament_date_time' => $this->tournament_date_time?->format('Y-m-d H:i'),
            'status' => $this->status,
            'venue' => $this->venue,
            'city' => $this->city,
            'country' => $this->country,
            'weight_class_name' => $this->weight_class_name,
            'rounds_scheduled' => $this->rounds_scheduled,
            'is_title_fight' => $this->is_title_fight,

            'fighter_red_id' => $this->fighter_red_id,
            'fighter_blue_id' => $this->fighter_blue_id,
            'winner_id' => $this->winner_id,

            'method_of_victory' => $this->method_of_victory,
            'round_of_finish' => $this->round_of_finish,
            'time_of_finish' => $this->time_of_finish,

            'fighter_red' => $this->whenLoaded('fighterRed', function () {
                return [
                    'id' => $this->fighterRed->id,
                    'nickname' => $this->fighterRed->nickname,
                    'avatar' => $this->fighterRed->avatar,
                    'date_of_birth' => $this->fighterRed->date_of_birth,
                    'wins' => $this->fighterRed->wins,
                    'draws' => $this->fighterRed->draws,
                    'losses' => $this->fighterRed->losses,
                    'wins_by_ko' => $this->fighterRed->wins_by_ko,
                ];
            }),

            'fighter_blue' => $this->whenLoaded('fighterBlue', function () {
                return [
                    'id' => $this->fighterBlue->id,
                    'nickname' => $this->fighterBlue->nickname,
                    'avatar' => $this->fighterBlue->avatar,
                    'date_of_birth' => $this->fighterBlue->date_of_birth,
                    'wins' => $this->fighterBlue->wins,
                    'draws' => $this->fighterBlue->draws,
                    'losses' => $this->fighterBlue->losses,
                    'wins_by_ko' => $this->fighterBlue->wins_by_ko,
                ];
            }),

            'winner' => $this->whenLoaded('winner', function () {
                return [
                    'id' => $this->winner->id,
                    'nickname' => $this->winner->nickname,
                    'avatar' => $this->winner->avatar,
                ];
            }),

            'videos' => VideoResource::collection($this->whenLoaded('videos')),
            'images'   => TournamentImageResource::collection($this->whenLoaded('images')),

            'meta_title'    => $this->meta_title,
            'meta_keywords' => $this->meta_keywords,
            'meta_desc'     => $this->meta_desc,

            'created_at' => $this->created_at?->toDateTimeString(),
            'updated_at' => $this->updated_at?->toDateTimeString(),
        ];
    }
}
