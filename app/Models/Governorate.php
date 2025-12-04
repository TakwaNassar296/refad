<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Translatable\HasTranslations;

class Governorate extends Model
{
    use HasFactory, HasTranslations, SoftDeletes;

    protected $fillable = [
        'name',
    ];

    public $translatable = ['name'];

    protected $casts = [
        'name' => 'array',
    ];

    public function camps()
    {
        return $this->hasMany(Camp::class);
    }

   
}