<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Translatable\HasTranslations;

class Testimonial extends Model
{
    use HasFactory, HasTranslations , SoftDeletes;

    public $translatable = [ 'opinion'];

    protected $fillable = [
        'user_name',
        'user_image',
        'opinion',
        'order',
    ];

   
}