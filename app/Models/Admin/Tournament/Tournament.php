<?php

namespace App\Models\Admin\Tournament;

use App\Models\Admin\Athlete\Athlete;
use App\Models\Admin\Video\Video;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Tournament extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'tournaments';

    protected $fillable = [
        'sort',
        'activity',
        'left',
        'main',
        'right',
        'locale',
        'name',
        'url',
        'short',
        'description',
        'tournament_date_time',
        'status',
        'venue',
        'city',
        'country',
        'weight_class_name',
        'rounds_scheduled',
        'is_title_fight',
        'fighter_red_id',
        'fighter_blue_id',
        'winner_id',
        'method_of_victory',
        'round_of_finish',
        'time_of_finish',
        'meta_title',
        'meta_keywords',
        'meta_desc',
    ];

    protected $casts = [
        'activity' => 'boolean',
        'left' => 'boolean',
        'main' => 'boolean',
        'right' => 'boolean',
        'sort' => 'integer',
        'is_title_fight' => 'boolean',
        'tournament_date_time' => 'datetime',
        'rounds_scheduled' => 'integer',
        'round_of_finish' => 'integer',
        'winner_id' => 'integer',
        'fighter_red_id' => 'integer',
        'fighter_blue_id' => 'integer',
        'status' => 'string',
    ];

    public function images(): BelongsToMany
    {
        return $this->belongsToMany(TournamentImage::class, 'tournament_has_images', 'tournament_id', 'image_id')
            ->withPivot('order')
            ->orderByPivot('order');
    }

    // 🟥 Спортсмен в красном углу
    public function fighterRed(): BelongsTo
    {
        return $this->belongsTo(Athlete::class, 'fighter_red_id');
    }

    // 🟦 Спортсмен в синем углу
    public function fighterBlue(): BelongsTo
    {
        return $this->belongsTo(Athlete::class, 'fighter_blue_id');
    }

    // 🏆 Победитель боя
    public function winner(): BelongsTo
    {
        return $this->belongsTo(Athlete::class, 'winner_id');
    }

    public function videos(): BelongsToMany
    {
        return $this->belongsToMany(Video::class, 'tournament_has_video', 'tournament_id', 'video_id' );
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

    public function scopeScheduled($query)
    {
        return $query->where('status', 'scheduled')->orderBy('tournament_date_time');
    }

    public function scopeLive($query)
    {
        return $query->where('status', 'live')->orderBy('tournament_date_time');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed')->orderByDesc('tournament_date_time');
    }

    public function scopeUpcoming($query)
    {
        return $query->where('status', 'scheduled')
            ->where('tournament_date_time', '>=', now())
            ->orderBy('tournament_date_time');
    }
}
