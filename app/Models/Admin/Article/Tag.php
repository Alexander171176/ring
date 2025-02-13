<?php

namespace App\Models\Admin\Article;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tag extends Model
{
    use HasFactory;

    protected $guarded = false;
    protected $table = 'tags';

    protected $fillable = [
        'name', // Название тега
        'slug', // URL-совместимое имя
        'locale'
    ];

    // Отношение многие ко многим с моделью Article
    public function articles(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Article::class, 'article_has_tag', 'tag_id', 'article_id');
    }
}
