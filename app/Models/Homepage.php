<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class Homepage extends Model
{
    use HasFactory , HasTranslations ;

    public $translatable = ['hero_title' , 'hero_description' , 'hero_subtitle']; 

    protected $fillable = [
        'hero_title',
        'hero_description', 
        'hero_image',
        'camps_count',
        'contributors_count',
        'projects_count',
        'families_count',
        'hero_subtitle'
    ];

 
}