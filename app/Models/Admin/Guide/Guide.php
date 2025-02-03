<?php

namespace App\Models\Admin\Guide;

use App\Models\Admin\Tutorial\Tutorial;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Guide extends Model
{
    use HasFactory;

    protected $guarded = false;
    protected $table = 'guides';

    protected $fillable = [
        'sort',
        'activity',
        'title',
        'url',
        'short',
        'description',
        'author',
        'tags',
        'views',
        'likes',
        'image_url',
        'seo_title',
        'seo_alt',
        'meta_title',
        'meta_keywords',
        'meta_desc',
    ];

    // Определите отношение многие ко многим с моделью Tutorial
    public function tutorials(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Tutorial::class, 'guide_has_tutorials');
    }

}
