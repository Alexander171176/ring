<?php

namespace App\Models\Admin\Video;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany; // <--- Добавили обратно для Likes
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use App\Models\Admin\Section\Section;
use App\Models\Admin\Article\Article;
use App\Models\Admin\Video\VideoImage;
use App\Models\User\Like\VideoLike; // <--- Добавили обратно для Likes

class Video extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia;

    // Оставляем явное указание таблицы для надежности
    protected $table = 'videos';

    // Используем fillable из новой версии, т.к. video_url управляется Spatie
    protected $fillable = [
        'sort',
        'locale',
        'title',
        'url',
        'short',
        'description',
        'author',
        'published_at',
        'duration',
        'source_type',
        // 'video_url', // Убираем или комментируем
        'external_video_id',
        'views',
        'likes', // Оставляем поле likes, если оно есть в таблице videos
        'meta_title',
        'meta_keywords',
        'meta_desc',
        'activity',
        'left',
        'main',
        'right',
    ];

    // Касты из новой версии
    protected $casts = [
        'activity' => 'boolean',
        'left' => 'boolean',
        'main' => 'boolean',
        'right' => 'boolean',
        'published_at' => 'date',
    ];

    // Регистрация медиа-коллекции из новой версии
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('videos')
            ->singleFile();
    }

    // --- Связи из СТАРОЙ (оригинальной) версии с правильными именами таблиц и ключей ---

    /**
     * Связь: Видео - Секции (многие ко многим)
     */
    public function sections(): BelongsToMany
    {
        return $this->belongsToMany(Section::class, 'section_has_video'); // <--- Правильное имя
    }

    /**
     * Связь: Видео - Статья (многие ко многим)
     */
    public function articles(): BelongsToMany
    {
        return $this->belongsToMany(Article::class, 'article_has_video'); // <--- Правильное имя
    }

    /**
     * Связь: Видео - Лайки (один ко многим) - Возвращаем эту связь
     */
    public function likes(): HasMany
    {
        // Убедитесь, что модель VideoLike существует и неймспейс правильный
        return $this->hasMany(VideoLike::class, 'video_id');
    }

    /**
     * Связь: Видео - Изображения (многие ко многим)
     */
    public function images(): BelongsToMany
    {
        // Используем имя таблицы и кастомные ключи из старой версии
        return $this->belongsToMany(VideoImage::class, 'video_has_images', 'video_id', 'image_id'); // <--- Правильные параметры
    }

    /**
     * Связь: Видео - Рекомендованные Видео (самоссылочная связь, многие ко многим)
     */
    public function relatedVideos(): BelongsToMany
    {
        // Используем имя таблицы и кастомные ключи из старой версии
        return $this->belongsToMany(self::class, 'video_related', 'video_id', 'related_video_id'); // <--- Правильные параметры
    }
    // --- Конец связей из старой версии ---
}
