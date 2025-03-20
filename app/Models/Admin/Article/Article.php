<?php

namespace App\Models\Admin\Article;

use App\Models\Admin\Comment\Comment;
use App\Models\Admin\Section\Section;
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
        'main',
        'sidebar',
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
    public function sections(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Section::class, 'article_has_section');
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

}
