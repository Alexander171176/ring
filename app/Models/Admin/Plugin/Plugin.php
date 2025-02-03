<?php

namespace App\Models\Admin\Plugin;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Plugin extends Model
{
    use HasFactory;

    protected $guarded = false;
    protected $table = 'plugins';

    protected $fillable = [
        'sort',
        'icon',
        'name',
        'version',
        'code',
        'options',
        'description',
        'readme',
        'templates',
        'activity',
    ];
}
