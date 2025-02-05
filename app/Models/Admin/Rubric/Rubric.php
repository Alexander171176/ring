<?php

namespace App\Models\Admin\Rubric;

use App\Models\Admin\Article\Article;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rubric extends Model
{
    use HasFactory;
    protected $guarded = false;
    protected $table = 'rubrics';

    protected $fillable = ['sort', 'activity', 'icon', 'views', 'image_url'];

    public function translations(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(RubricTranslation::class);
    }

    public function translation($locale = null): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(RubricTranslation::class)->where('locale', $locale ?? app()->getLocale());
    }

    // Определите отношение многие ко многим с моделью Article
    public function articles(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Article::class, 'article_has_rubrics');
    }
}
