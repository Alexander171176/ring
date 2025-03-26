<?php

namespace App\Models\Admin\Comment;

use App\Models\Admin\Article\Article;
use App\Models\Admin\Section\Section;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Comment extends Model
{
    use HasFactory;

    protected $guarded = false; // Вы можете использовать либо guarded, либо fillable
    protected $table = 'comments';

    protected $fillable = [
        'user_id',
        'article_id',
        'section_id',
        'parent_id',
        'content',
        'status',
        'activity',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id'); // Связываем с таблицей users через user_id
    }

    public function article(): BelongsTo
    {
        return $this->belongsTo(Article::class);
    }

    public function section(): BelongsTo
    {
        return $this->belongsTo(Section::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Comment::class, 'parent_id');
    }

    public function replies(): HasMany
    {
        return $this->hasMany(Comment::class, 'parent_id');
    }
}
