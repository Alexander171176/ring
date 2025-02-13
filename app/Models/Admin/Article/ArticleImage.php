<?php

namespace App\Models\Admin\Article;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ArticleImage extends Model
{
    use HasFactory;

    protected $guarded = false;
    protected $table = 'article_images';

    protected $fillable = [
        'path',    // Путь к изображению
        'alt',     // Альтернативный текст
        'caption', // Подпись к изображению
    ];

    // Отношение многие ко многим с моделью Article
    public function articles(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Article::class, 'article_has_images', 'image_id', 'article_id');
    }
}
