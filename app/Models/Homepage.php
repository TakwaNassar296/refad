<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Homepage extends Model
{
    use HasFactory, HasTranslations;

    public $translatable = ['title', 'description'];

    protected $fillable = [
        'camps_count',
        'contributors_count',
        'projects_count',
        'families_count',
        'title', 'description',
        'complaint_image',
        'contact_image',
    ];

    public function slides()
    {
        return $this->hasMany(HomepageSlide::class);
    }

    public function sections()
    {
        return $this->hasMany(HomepageSection::class);
    }


 
}