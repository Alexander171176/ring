<?php

namespace App\Models\Admin\Tournament; // Корректное пространство имен

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Admin\Athlete\Athlete; // Импортируем модель Athlete

class Tournament extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * Имя таблицы, связанной с моделью.
     * @var string
     */
    protected $table = 'tournaments';

    // Константы для статусов для лучшей читаемости и поддержки
    public const STATUS_SCHEDULED = 'scheduled';
    public const STATUS_LIVE = 'live';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_POSTPONED = 'postponed';
    public const STATUS_CANCELLED = 'cancelled';

    /**
     * Атрибуты, которые можно массово присваивать.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'sort',
        'activity',
        'locale',
        'type',
        'parent_tournament_id',
        'name',
        'tournament_date_time',
        'status',
        'venue',
        'city',
        'country',
        'short',
        'description',
        'weight_class_name',
        'rounds_scheduled',
        'is_title_fight',
        'winner_id',
        'method_of_victory',
        'round_of_finish',
        'time_of_finish',
        'details',
        'is_main_card_event',
    ];

    /**
     * Атрибуты, которые должны быть преобразованы к нативным типам.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'activity' => 'boolean',
        'sort' => 'integer',
        'tournament_date_time' => 'datetime', // Преобразование в объект Carbon/DateTime
        'is_title_fight' => 'boolean',
        'is_main_card_event' => 'boolean',
        'rounds_scheduled' => 'integer',
        'round_of_finish' => 'integer', // Может быть и string, если "N/A" или что-то такое
        'parent_tournament_id' => 'integer',
        'winner_id' => 'integer',
        'type' => 'string',
    ];

    /**
     * Связь с "обертками" изображений TournamentImage.
     * Турнир имеет много TournamentImage через пивотную таблицу.
     */
    public function images(): BelongsToMany
    {
        return $this->belongsToMany(TournamentImage::class, 'tournament_has_images', 'tournament_id', 'image_id')
            ->withPivot('order')
            ->orderByPivot('order', 'asc');
    }

    /**
     * Связь с родительским турниром (если это поединок в карде или сам кард).
     * Связь "принадлежит-к" (один-к-одному или один-ко-многим в обратную сторону).
     */
    public function parentTournament(): BelongsTo
    {
        return $this->belongsTo(Tournament::class, 'parent_tournament_id');
    }

    /**
     * Связь с дочерними турнирами/поединками (если это главный турнир или кард).
     * Связь "один-ко-многим".
     */
    public function childTournaments(): HasMany
    {
        return $this->hasMany(Tournament::class, 'parent_tournament_id');
    }

    /**
     * Спортсмены, участвующие в этом турнире/поединке.
     * Связь "многие-ко-многим".
     */
    public function athletes(): BelongsToMany
    {
        return $this->belongsToMany(Athlete::class, 'athlete_has_tournament', 'tournament_id', 'athlete_id')
            ->withPivot(['corner', 'is_headliner', 'weight_at_weigh_in_kg'])
            ->withTimestamps();
    }

    /**
     * Спортсмен-победитель этого поединка.
     * Связь "принадлежит-к".
     */
    public function winner(): BelongsTo
    {
        return $this->belongsTo(Athlete::class, 'winner_id');
    }

    // --- Скоупы (Scopes) ---

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
        return $query->where('status', self::STATUS_SCHEDULED)->orderBy('tournament_date_time', 'asc');
    }

    public function scopeLive($query)
    {
        return $query->where('status', self::STATUS_LIVE)->orderBy('tournament_date_time', 'asc');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', self::STATUS_COMPLETED)->orderBy('tournament_date_time', 'desc');
    }

    public function scopeUpcoming($query)
    {
        return $query->where('status', self::STATUS_SCHEDULED)
            ->where('tournament_date_time', '>=', now())
            ->orderBy('tournament_date_time', 'asc');
    }

    /**
     * Выбирает только турниры/поединки, которые являются "боями" (не являются картами/контейнерами).
     * Это можно определить, например, по наличию weight_class_name или отсутствию дочерних элементов.
     * Или если is_main_card_event = false (если вы используете это поле для карт).
     * Более надежно, если у турнира есть атлеты.
     */
    public function scopeIsFight($query)
    {
        // Пример: поединок, если у него есть связанный tournament_type_id,
        // и он НЕ является событием типа "карта" (если is_main_card_event это означает)
        // или у него нет дочерних событий (parent_tournament_id для других событий НЕ равен id этого события).
        // Самый простой способ - если у него есть спортсмены-участники.
        return $query->whereHas('athletes'); // Показывает только те 'tournaments', у которых есть связанные 'athletes'
        //->where('is_main_card_event', false); // Если вы так различаете
    }

    /**
     * Выбирает только турниры, которые являются "картами" или главными событиями-контейнерами.
     */
    public function scopeIsTournamentCard($query)
    {
        // Пример: это карта, если у нее есть дочерние турниры/поединки.
        return $query->whereHas('childTournaments');
        //->orWhere('is_main_card_event', true); // Если вы так различаете
    }
}
