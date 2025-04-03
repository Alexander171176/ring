<?php

namespace App\Models\Admin\Banner;

use App\Models\Admin\Section\Section;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Banner extends Model
{
    use HasFactory;

    protected $guarded = false;
    protected $table = 'banners';

    protected $fillable = [
        'sort',
        'activity',
        'left',
        'right',
        'title',
        'link',
        'short',
        'comment',
    ];

    /**
     * Связь: Баннер - Секции (многие ко многим)
     */
    public function sections(): BelongsToMany
    {
        return $this->belongsToMany(Section::class, 'banner_has_section');
    }

    /**
     * Связь: Баннер - Изображения (многие ко многим)
     */
    public function images(): BelongsToMany
    {
        return $this->belongsToMany(BannerImage::class, 'banner_has_images', 'banner_id', 'image_id');
    }
}
