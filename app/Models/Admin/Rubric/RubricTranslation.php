<?php

namespace App\Models\Admin\Rubric;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RubricTranslation extends Model
{
    use HasFactory;
    protected $guarded = false;
    protected $table = 'rubric_translations';

    protected $fillable = [
        'rubric_id',
        'locale',
        'title',
        'url',
        'short',
        'description',
        'seo_title',
        'seo_alt',
        'meta_title',
        'meta_keywords',
        'meta_desc'];

    public function rubric(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Rubric::class);
    }
}
