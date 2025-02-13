<?php

namespace App\Models\Admin\Article;

use App\Models\Admin\Comment\Comment;
use App\Models\Admin\Rubric\Rubric;
use App\Models\Admin\Article\Tag;
use App\Models\Admin\Article\ArticleImage;
use App\Models\User\Like\ArticleLike;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Article extends Model
{
    use HasFactory;

    protected $guarded = false;
    protected $table = 'articles';

    protected $fillable = [
        'sort',
        'activity',
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
     * Связь: Статья - Рубрики (многие ко многим)
     */
    public function rubrics(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Rubric::class, 'article_has_rubrics');
    }

    /**
     * Связь: Статья - Комментарии (один ко многим)
     */
    public function comments(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Comment::class);
    }

    /**
     * Связь: Статья - Лайки (один ко многим)
     */
    public function likes(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(ArticleLike::class, 'article_id');
    }

    /**
     * Связь: Статья - Теги (многие ко многим)
     */
    public function tags(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Tag::class, 'article_has_tag', 'article_id', 'tag_id');
    }

    /**
     * Связь: Статья - Изображения (многие ко многим)
     */
    public function images(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(ArticleImage::class, 'article_has_images', 'article_id', 'image_id');
    }

    /**
     * Скоуп: Только активные статьи
     */
    public function scopeActive($query)
    {
        return $query->where('activity', true);
    }

    /**
     * Скоуп: Сортировка по количеству просмотров
     */
    public function scopeSortByViews($query, $direction = 'desc')
    {
        return $query->orderBy('views', $direction);
    }

    /**
     * Скоуп: Сортировка по количеству лайков
     */
    public function scopeSortByLikes($query, $direction = 'desc')
    {
        return $query->orderBy('likes', $direction);
    }

    /**
     * Подсчет комментариев
     */
    public function getCommentsCountAttribute(): int
    {
        return $this->comments()->count();
    }

    /**
     * Подсчет лайков
     */
    public function getLikesCountAttribute(): int
    {
        return $this->likes()->count();
    }

    /**
     * Подсчет количества тегов
     */
    public function getTagsCountAttribute(): int
    {
        return $this->tags()->count();
    }
}
