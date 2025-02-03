<?php

namespace App\Models\Admin\Article;

use App\Models\Admin\Comment\Comment;
use App\Models\Admin\Rubric\Rubric;
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
        'title',
        'url',
        'short',
        'description',
        'author',
        'tags',
        'views',
        'likes',
        'image_url',
        'seo_title',
        'seo_alt',
        'meta_title',
        'meta_keywords',
        'meta_desc',
    ];

    // Определите отношение многие ко многим с моделью Rubric
    public function rubrics(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Rubric::class, 'article_has_rubrics');
    }

    // Определите отношение с моделью Comment
    public function comments(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Comment::class);
    }

    // Определите отношение с моделью ArticleLike
    public function likes(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(ArticleLike::class, 'article_id');
    }
}
