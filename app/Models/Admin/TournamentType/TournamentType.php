<?php

namespace App\Models\Admin\TournamentType; // Корректное пространство имен

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Admin\Tournament\Tournament; // Импортируем модель Tournament

class TournamentType extends Model
{
    use HasFactory;

    /**
     * Имя таблицы, связанной с моделью.
     * @var string
     */
    protected $table = 'tournament_types';

    /**
     * Атрибуты, которые можно массово присваивать.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'sort',
        'activity',
        'name',
        'short',
        'description',
    ];

    /**
     * Атрибуты, которые должны быть преобразованы к нативным типам.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'activity' => 'boolean',
        'sort' => 'integer',
    ];

    /**
     * Получить все турниры/поединки, относящиеся к этому типу.
     * Связь "один-ко-многим".
     */
    public function tournaments(): HasMany
    {
        return $this->hasMany(Tournament::class, 'tournament_type_id');
    }

    // Скоупы
    public function scopeActive($query)
    {
        return $query->where('activity', true);
    }

    public function scopeOrdered($query, string $direction = 'asc')
    {
        return $query->orderBy('sort', $direction)->orderBy('id', $direction);
    }
}
