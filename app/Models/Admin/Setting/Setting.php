<?php

namespace App\Models\Admin\Setting;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

// Импортируем Attribute для кастинга JSON
use Illuminate\Support\Facades\Cache;

// Импортируем Cache
use Illuminate\Support\Facades\Log;

// Импортируем Log

class Setting extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'settings';

    /**
     * The attributes that are mass assignable.
     * Определяем $fillable, так как $guarded по умолчанию true (или если вы явно его установите).
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'type',
        'option',
        'value',
        'constant',
        'category',
        'description',
        'activity',
    ];

    /**
     * The attributes that should be hidden for serialization.
     * Поля, которые не должны попадать в JSON/массивы по умолчанию (например, при отдаче через API).
     *
     * @var array<int, string>
     */
    protected $hidden = [
        // Можно скрыть, если не нужно во внешних ответах
        'created_at',
        'updated_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'activity' => 'boolean',
        'value' => 'json', // Раскомментируйте, если 'value' часто является JSON
    ];

    /*
     * Аксессор/мутатор для value НЕ НУЖЕН, если используется 'value' => 'json' в $casts
     */
    // protected function value(): Attribute
    // {
    //     ...
    // }

    /**
     * Очистка кэша настроек при обновлении или удалении записи.
     */
    protected static function booted(): void
    {
        static::saved(function (Setting $setting) {
            // TODO: Заменить 'site_settings' на реальный ключ кэша настроек
            // Cache::forget('site_settings');
            Log::info("Setting saved, cache cleared: " . $setting->option);
        });

        static::deleted(function (Setting $setting) {
            // TODO: Заменить 'site_settings' на реальный ключ кэша настроек
            // Cache::forget('site_settings');
            Log::info("Setting deleted, cache cleared: " . $setting->option);
        });
    }
}
