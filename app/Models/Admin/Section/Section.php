<?php

namespace App\Models\Admin\Section;

use App\Models\Admin\Article\Article;
use App\Models\Admin\Rubric\Rubric;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
    public function rubrics(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Rubric::class, 'rubric_has_sections');
    }

    // Определите отношение многие ко многим с моделью Article
    public function articles(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Article::class, 'article_has_section', 'section_id', 'article_id');
    }
}
