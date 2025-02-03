<?php

namespace App\Models\Admin\Page;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Page extends Model
{
    use HasFactory;
    protected $guarded = false;
    protected $table = 'pages';

    protected $fillable = [
        'sort',
        'title',
        'url',
        'slug',
        'content',
        'meta_title',
        'meta_keywords',
        'meta_desc',
        'activity',
        'print_in_menu',
        'without_style',
        'parent_id',
    ];

    public function parent(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Page::class, 'parent_id');
    }

    public function children(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Page::class, 'parent_id');
    }
}
