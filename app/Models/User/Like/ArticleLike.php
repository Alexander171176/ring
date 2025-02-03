<?php

namespace App\Models\User\Like;

use App\Models\Admin\Article\Article;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ArticleLike extends Model
{
    use HasFactory;

    protected $guarded = false;
    protected $table = 'article_likes';

    protected $fillable = ['user_id', 'article_id'];

    // Убедитесь, что есть отношение к статье
    public function article(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Article::class);
    }

    // Отношение к пользователю (если есть)
    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
