<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class HomepageSection extends Model
{
    use HasFactory, HasTranslations;

    public $translatable = ['title', 'description'];

    protected $fillable = [
        'homepage_id',
        'title',
        'description',
        'image',
    ];

    public function homepage()
    {
        return $this->belongsTo(Homepage::class);
    }
}
