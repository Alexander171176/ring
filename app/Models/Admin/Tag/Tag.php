<?php

namespace App\Models\Admin\Tag;

use App\Models\Admin\Article\Article;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Tag extends Model
{
    use HasFactory;

    protected $guarded = false;
    protected $table = 'tags';

    protected $fillable = [
        'locale', // Язык (ru, en, kz)
        'name', // Название тега
        'slug', // URL-совместимое имя
        'short', // Краткое Описание
        'description', // Описание
        'meta_title', // meta title
        'meta_keywords', // meta keywords
        'meta_desc', // meta description
    ];

    // Отношение многие ко многим с моделью Article
    public function articles(): BelongsToMany
    {
        return $this->belongsToMany(Article::class, 'article_has_tag', 'tag_id', 'article_id');
    }
}
