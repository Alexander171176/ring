<?php

namespace App\Models\Admin\Article;

use App\Models\Admin\Comment\Comment;
use App\Models\Admin\Section\Section;
use App\Models\Admin\Tag\Tag;
use App\Models\Admin\Video\Video;
use App\Models\User\Like\ArticleLike;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Article extends Model
{
    use HasFactory;

    protected $guarded = false;
    protected $table = 'articles';

    protected $fillable = [
        'sort',
        'activity',
        'left',
        'main',
        'right',
        'locale',
        'title',
        'url',
        'short',
        'description',
        'author',
        'views',
        'likes',
        'meta_title',
        'meta_keywords',
        'meta_desc',
    ];

    /**
     * Связь: Статья - Секции (многие ко многим)
     */
    public function sections(): BelongsToMany
    {
        return $this->belongsToMany(Section::class, 'article_has_section');
    }

    /**
     * Связь: Статья - Комментарии (один ко многим)
     */
    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    /**
     * Связь: Статья - Лайки (один ко многим)
     */
    public function likes(): HasMany
    {
        return $this->hasMany(ArticleLike::class, 'article_id');
    }

    /**
     * Связь: Статья - Теги (многие ко многим)
     */
    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class, 'article_has_tag', 'article_id', 'tag_id');
    }

    /**
     * Связь: Статья - Изображения (многие ко многим)
     */
    public function images(): BelongsToMany
    {
        return $this->belongsToMany(ArticleImage::class, 'article_has_images', 'article_id', 'image_id');
    }

    /**
     * Видео, привязанные к статье.
     */
    public function videos(): BelongsToMany
    {
        return $this->belongsToMany(Video::class, 'video_has_article');
    }

    /**
     * Связь: Статья - Рекомендованные статьи (самоссылочная связь, многие ко многим)
     */
    public function relatedArticles(): BelongsToMany
    {
        return $this->belongsToMany(self::class, 'article_related', 'article_id', 'related_article_id');
    }

}
