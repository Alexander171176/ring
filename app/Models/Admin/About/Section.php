<?php

namespace App\Models\Admin\About;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Section extends Model
{
    use HasFactory;

    protected $table = 'sections';
    protected $guarded = [];  // Рекомендуется использовать guarded вместо guarded = false

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'type',
        'tailwind',
        'image',
        'title',
        'content',
        'sort',
        'activity'
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'activity' => 'boolean',
        'sort' => 'integer',
    ];
}
