<?php

namespace App\Models\Admin\Athlete;

use App\Models\Admin\Tournament\Tournament;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Athlete extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'athletes';

    protected $fillable = [
        'sort',
        'activity',
        'locale',
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
        'activity' => 'boolean',
        'sort' => 'integer',
        'date_of_birth' => 'date',
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

    // 🖼 Связь с изображениями (если используется)
    public function images(): BelongsToMany
    {
        return $this->belongsToMany(AthleteImage::class, 'athlete_has_images', 'athlete_id', 'image_id')
            ->withPivot('order')
            ->orderByPivot('order', 'asc');
    }

    // 🔴 Поединки, где атлет — в красном углу
    public function redFights(): HasMany
    {
        return $this->hasMany(Tournament::class, 'fighter_red_id');
    }

    // 🔵 Поединки, где атлет — в синем углу
    public function blueFights(): HasMany
    {
        return $this->hasMany(Tournament::class, 'fighter_blue_id');
    }

    // 🏆 Победы
    public function wonFights(): HasMany
    {
        return $this->hasMany(Tournament::class, 'winner_id');
    }

    // 🔠 Полное имя для вывода
    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }

    // --- Скоупы ---

    public function scopeActive($query)
    {
        return $query->where('activity', true);
    }

    public function scopeOrdered($query, string $direction = 'asc')
    {
        return $query->orderBy('sort', $direction)->orderBy('id', $direction);
    }
}
