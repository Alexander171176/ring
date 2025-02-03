<?php

namespace App\Models\Admin\Tutorial;

use App\Models\Admin\Guide\Guide;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tutorial extends Model
{
    use HasFactory;

    protected $guarded = false;
    protected $table = 'tutorials';

    protected $fillable = [
        'sort',
        'activity',
        'icon',
        'title',
        'url',
        'short',
        'description',
        'views',
        'image_url',
        'seo_title',
        'seo_alt',
        'meta_title',
        'meta_keywords',
        'meta_desc',
    ];

    // Определите отношение многие ко многим с моделью Guide
    public function articles(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Guide::class, 'guide_has_tutorials');
    }
}
