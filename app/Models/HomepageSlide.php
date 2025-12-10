<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class HomepageSlide extends Model
{
    use HasFactory, HasTranslations;

    public $translatable = ['hero_title', 'hero_description', 'hero_subtitle'];

    protected $fillable = [
        'homepage_id',
        'hero_title',
        'hero_description',
        'hero_image',
        'small_hero_image',
        'hero_subtitle'
    ];

    public function homepage()
    {
        return $this->belongsTo(Homepage::class);
    }
}
