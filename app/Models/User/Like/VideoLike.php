<?php

namespace App\Models\User\Like;

use App\Models\Admin\Video\Video;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VideoLike extends Model
{
    use HasFactory;

    protected $guarded = false;
    protected $table = 'video_likes';

    protected $fillable = ['user_id', 'video_id'];

    // Убедитесь, что есть отношение к статье
    public function video(): BelongsTo
    {
        return $this->belongsTo(Video::class, 'video_id');
    }

    // Отношение к пользователю (если есть)
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
