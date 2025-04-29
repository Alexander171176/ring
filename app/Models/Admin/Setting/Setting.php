<?php

namespace App\Models\Admin\Setting;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute; // Импортируем Attribute
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class Setting extends Model
{
    use HasFactory;
    protected $table = 'settings';
    protected $fillable = [
        'sort',
        'activity',
        'type', // Теперь это поле важно для преобразования
        'option',
        'value',
        'constant',
        'category',
        'description',
    ];
    protected $hidden = ['created_at', 'updated_at'];

    /**
     * The attributes that should be cast.
     * Кастуем только activity. Поле value будет обрабатываться аксессором/мутатором.
     */
    protected $casts = [
        'sort' => 'integer',
        'activity' => 'boolean',
    ];

    /**
     * Аксессор и Мутатор для поля 'value'.
     * Преобразует значение В PHP тип на основе поля 'type' при чтении.
     * Преобразует значение ИЗ PHP типа в строку перед сохранением в БД.
     */
    protected function value(): Attribute
    {
        return Attribute::make(
            get: function ($value) {
                if (is_null($value)) {
                    return null;
                }
                // Преобразуем на основе поля 'type' текущей модели
                switch ($this->type) {
                    case 'checkbox': // Если тип 'checkbox' или 'boolean'
                    case 'boolean':
                        // filter_var надежнее для '1', '0', 'true', 'false', 'on', 'off'
                        return filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? false;
                    case 'number': // Если тип 'number' или 'integer' или 'float'
                    case 'integer':
                    case 'float':
                        // is_numeric пропускает строки, похожие на числа
                        return is_numeric($value) ? ($value == (int)$value ? (int)$value : (float)$value) : null; // Возвращаем int/float или null если не число
                    case 'json': // Если тип явно указан как JSON
                    case 'array':
                        // Пытаемся декодировать JSON, если не удается - возвращаем как есть (или null?)
                        return json_decode($value, true) ?? $value; // Возвращаем массив или исходную строку
                    case 'string': // Если тип 'string', 'text', 'select' или не указан
                    case 'text':
                    case 'select':
                    default:
                        return (string)$value; // Возвращаем как строку
                }
            },
            set: function ($value): ?string {
                // Преобразуем обратно в строку перед сохранением
                if (is_bool($value)) {
                    // Boolean преобразуем в '1' или '0'
                    return $value ? '1' : '0';
                }
                if (is_null($value)) {
                    return null;
                }
                // Если это массив или объект, кодируем в JSON (даже если type не json)
                // Это нужно, если фронтенд присылает массив для полей типа select (multiple)
                if (is_array($value) || is_object($value)) {
                    return json_encode($value);
                }
                // Все остальное приводим к строке
                return (string)$value;
            }
        );
    }

    protected static function booted(): void
    {
        static::saved(function (Setting $setting) {
            // TODO: Заменить 'site_settings' на реальный ключ кэша настроек
            Cache::forget('site_settings'); // Забываем общий кэш
            Cache::forget('setting_' . $setting->option); // Забываем кэш конкретной настройки
            Log::info("Настройки сохранены, кэш очищен: " . $setting->option);
        });

        static::deleted(function (Setting $setting) {
            // TODO: Заменить 'site_settings' на реальный ключ кэша настроек
            Cache::forget('site_settings');
            Cache::forget('setting_' . $setting->option);
            Log::info("Настройка удалена, кэш очищен: " . $setting->option);
        });
    }
}
