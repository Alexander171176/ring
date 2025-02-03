<?php

namespace App\Models\Admin\Contact;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Contact extends Model
{
    use HasFactory;

    protected $guarded = false;
    protected $table = 'contacts';

    protected $fillable = [
        'image',
        'tailwind',
        'title',
        'content'
    ];

}
