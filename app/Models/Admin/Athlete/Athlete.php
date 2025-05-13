<?php

namespace App\Models\Admin\Athlete;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Admin\Tournament\Tournament;
use App\Models\Admin\Athlete\AthleteImage;

class Athlete extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'athletes';

    protected $fillable = [
        'sort',
        'activity',
        'first_name',
        'last_name',
        'nickname',
        'date_of_birth',
        'nationality',
        'height_cm',
        'reach_cm',
        'stance',
        'bio',
        'avatar',
        'wins',
        'losses',
        'draws',
        'no_contests',
        'wins_by_ko',
        'wins_by_submission',
        'wins_by_decision',
        'short',
        'description',
    ];

    protected $casts = [
        'date_of_birth' => 'date', // Преобразование в объект Carbon/Date
        'activity' => 'boolean',   // Преобразование в boolean
        'sort' => 'integer',
        'height_cm' => 'integer',
        'reach_cm' => 'integer',
        'wins' => 'integer',
        'losses' => 'integer',
        'draws' => 'integer',
        'no_contests' => 'integer',
        'wins_by_ko' => 'integer',
        'wins_by_submission' => 'integer',
        'wins_by_decision' => 'integer',
    ];

    /**
     * Связь с "обертками" изображений AthleteImage.
     * Атлет имеет много AthleteImage через пивотную таблицу.
     */
    public function images(): BelongsToMany
    {
        return $this->belongsToMany(AthleteImage::class, 'athlete_has_images', 'athlete_id', 'image_id')
            ->withPivot('order')
            ->orderByPivot('order', 'asc');
    }

    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }

    public function tournaments(): BelongsToMany
    {
        return $this->belongsToMany(Tournament::class, 'athlete_has_tournament', 'athlete_id', 'tournament_id')
            ->withPivot(['corner', 'is_headliner', 'weight_at_weigh_in_kg']);
    }

    public function wonTournaments(): HasMany
    {
        return $this->hasMany(Tournament::class, 'winner_id');
    }

    public function scopeActive($query): Builder
    {
        return $query->where('activity', true);
    }

    public function scopeOrdered($query, string $direction = 'asc'): Builder
    {
        return $query->orderBy('sort', $direction)->orderBy('id', $direction);
    }
}
