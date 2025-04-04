<?php

namespace App\Models\Admin\Section;

use App\Models\Admin\Article\Article;
use App\Models\Admin\Banner\Banner;
use App\Models\Admin\Rubric\Rubric;
use App\Models\Admin\Video\Video;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Section extends Model
{
    use HasFactory;

    protected $guarded = false;
    protected $table = 'sections';

    protected $fillable = [
        'sort',
        'activity',
        'icon',
        'locale',
        'title',
        'short',
    ];

    /**
     * Связь: Секция - Рубрики (многие ко многим)
     */
    public function rubrics(): BelongsToMany
    {
        return $this->belongsToMany(Rubric::class, 'rubric_has_sections');
    }

    // Определите отношение многие ко многим с моделью Article
    public function articles(): BelongsToMany
    {
        return $this->belongsToMany(Article::class, 'article_has_section', 'section_id', 'article_id');
    }

    // Определите отношение многие ко многим с моделью Banner
    public function banners(): BelongsToMany
    {
        return $this->belongsToMany(Banner::class, 'banner_has_section');
    }

    // Определите отношение многие ко многим с моделью Video
    public function videos(): BelongsToMany
    {
        return $this->belongsToMany(Video::class, 'video_has_section', 'section_id', 'video_id');
    }
}
