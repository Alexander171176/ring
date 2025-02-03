<?php

namespace App\Models\Admin\Setting;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use HasFactory;

    protected $guarded = false;
    protected $table = 'settings';

    protected $fillable = [
        'type',
        'option',
        'value',
        'constant',
        'category',
        'description',
        'activity',
    ];
}
