<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Page extends Model
{
    use HasFactory, HasTranslations;
    
    public $translatable = ['title', 'description'];

    protected $fillable = [
        'type',
        'title',
        'description',
        'image',
        'file', 
    ];

}