<?php

namespace App\Models\Admin\Video;

use App\Models\Admin\Article\Article;
use App\Models\Admin\Section\Section;
use App\Models\User\Like\VideoLike;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Video extends Model
{
    use HasFactory;

    protected $guarded = false;
    protected $table = 'videos';

    protected $fillable = [
        'sort',
        'activity',
        'left',
        'main',
        'right',
        'locale',
        'title',
        'url',
        'description',
        'author',
        'published_at',
        'duration',
        'source_type',
        'video_url',
        'external_video_id',
        'views',
        'likes',
        'meta_title',
        'meta_keywords',
        'meta_desc',
    ];

    /**
     * Связь: Видео - Секции (многие ко многим)
     */
    public function sections(): BelongsToMany
    {
        return $this->belongsToMany(Section::class, 'video_has_section');
    }

    /**
     * Связь: Видео - Статья (многие ко многим)
     */
    public function articles(): BelongsToMany
    {
        return $this->belongsToMany(Article::class, 'video_has_article');
    }

    /**
     * Связь: Видео - Лайки (один ко многим)
     */
    public function likes(): HasMany
    {
        return $this->hasMany(VideoLike::class, 'video_id');
    }

    /**
     * Связь: Видео - Изображения (многие ко многим)
     */
    public function images(): BelongsToMany
    {
        return $this->belongsToMany(ImageVideo::class, 'image_has_videos', 'video_id', 'image_id');
    }

    /**
     * Связь: Статья - Рекомендованные статьи (самоссылочная связь, многие ко многим)
     */
    public function relatedVideos(): BelongsToMany
    {
        return $this->belongsToMany(self::class, 'video_related', 'related_video_id', 'video_id');
    }
}
