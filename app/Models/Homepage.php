<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class Homepage extends Model
{
    use HasFactory , HasTranslations ;

    public $translatable = ['hero_title' , 'hero_description']; 

    protected $fillable = [
        'hero_title',
        'hero_description', 
        'hero_image',
        'families_count',
        'contributors_count',
        'projects_count',
        'familz_count',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean'
    ];
}