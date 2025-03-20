<?php

namespace App\Models\Admin\Rubric;

use App\Models\Admin\Article\Article;
use App\Models\Admin\Section\Section;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rubric extends Model
{
    use HasFactory;

    protected $guarded = false;
    protected $table = 'rubrics';

    protected $fillable = [
        'sort',
        'activity',
        'icon',
        'locale',
        'title',
        'url',
        'short',
        'meta_title',
        'meta_keywords',
        'meta_desc'
    ];

    // Определите отношение многие ко многим с моделью Section
    public function sections(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Section::class, 'rubric_has_sections', 'rubric_id', 'section_id');
    }

    // Определите отношение многие ко многим с моделью Article
    public function articles(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Article::class, 'article_has_rubrics', 'rubric_id', 'article_id');
    }

}
