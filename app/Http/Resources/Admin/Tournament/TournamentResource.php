<?php

namespace App\Http\Resources\Admin\Tournament;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TournamentResource extends JsonResource
{
    /**
     * Преобразование ресурса в массив.
     *
     * @param  Request  $request
     * @return array<string, mixed>
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'sort' => $this->sort,
            'activity' => $this->activity,
            'locale' => $this->locale,
            'name' => $this->name,
            'short' => $this->short,
            'description' => $this->description,
            'tournament_date_time' => $this->tournament_date_time?->format('Y-m-d'),
            'status' => $this->status,
            'venue' => $this->venue,
            'city' => $this->city,
            'country' => $this->country,
            'weight_class_name' => $this->weight_class_name,
            'rounds_scheduled' => $this->rounds_scheduled,
            'is_title_fight' => $this->is_title_fight,

            // ID бойцов — нужно для привязки к селектам
            'fighter_red_id' => $this->fighter_red_id,
            'fighter_blue_id' => $this->fighter_blue_id,
            'winner_id' => $this->winner_id,

            'method_of_victory' => $this->method_of_victory,
            'round_of_finish' => $this->round_of_finish,
            'time_of_finish' => $this->time_of_finish,

            // Дополнительные данные бойцов
            'fighter_red' => $this->whenLoaded('fighterRed', function () {
                return [
                    'id' => $this->fighterRed->id,
                    'nickname' => $this->fighterRed->nickname,
                    'avatar' => $this->fighterRed->avatar,
                ];
            }),

            'fighter_blue' => $this->whenLoaded('fighterBlue', function () {
                return [
                    'id' => $this->fighterBlue->id,
                    'nickname' => $this->fighterBlue->nickname,
                    'avatar' => $this->fighterBlue->avatar,
                ];
            }),

            'winner' => $this->whenLoaded('winner', function () {
                return [
                    'id' => $this->winner->id,
                    'nickname' => $this->winner->nickname,
                    'avatar' => $this->winner->avatar,
                ];
            }),

            // Изображения (если подгружены)
            'images' => $this->whenLoaded('images'),

            'created_at' => $this->created_at?->toDateTimeString(),
            'updated_at' => $this->updated_at?->toDateTimeString(),
        ];
    }
}
