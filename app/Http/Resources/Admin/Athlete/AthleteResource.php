<?php

namespace App\Http\Resources\Admin\Athlete;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AthleteResource extends JsonResource
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
            'sort' => $this->sort,
            'activity' => (bool) $this->activity,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'full_name' => $this->full_name, // Аксессор
            'nickname' => $this->nickname,
            'date_of_birth' => $this->date_of_birth?->format('Y-m-d'),
            'nationality' => $this->nationality,
            'height_cm' => $this->height_cm,
            'reach_cm' => $this->reach_cm,
            'stance' => $this->stance,
            'bio' => $this->bio,
            // 'avatar' => $this->avatar ? asset('storage/' . $this->avatar) : null, // Если avatar хранится в public/storage
            // 'avatar' => $this->avatar, // Или если 'avatar' это полный URL, то просто $this->avatar
            'wins' => $this->wins,
            'losses' => $this->losses,
            'draws' => $this->draws,
            'no_contests' => $this->no_contests,
            'wins_by_ko' => $this->wins_by_ko,
            'wins_by_submission' => $this->wins_by_submission,
            'wins_by_decision' => $this->wins_by_decision,
            'short' => $this->short,
            'description' => $this->description,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),

            // Связанные изображения
            // Используем whenLoaded, чтобы изображения загружались только если они были eager-loaded
            // или явно запрошены ($athlete->load('images'))
            'images' => AthleteImageResource::collection($this->whenLoaded('images')),

            // Можно добавить другие связанные данные, если нужно, например, статистику по турнирам
            // 'tournaments_count' => $this->whenCounted('tournaments'),
            // 'won_tournaments_count' => $this->whenCounted('wonTournaments'),
        ];
    }
}
