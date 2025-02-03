<?php

namespace App\Models\Admin\Comment;

use App\Models\Admin\Article\Article;
use App\Models\Admin\Rubric\Rubric;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    use HasFactory;

    protected $guarded = false; // Вы можете использовать либо guarded, либо fillable
    protected $table = 'comments';

    protected $fillable = [
        'user_id',
        'article_id',
        'rubric_id',
        'parent_id',
        'content',
        'status',
        'activity',
    ];

    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id'); // Связываем с таблицей users через user_id
    }

    public function article(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Article::class);
    }

    public function rubric(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Rubric::class);
    }

    public function parent(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Comment::class, 'parent_id');
    }

    public function replies(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Comment::class, 'parent_id');
    }
}
