<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Homepage extends Model
{
    use HasFactory  ;

    protected $fillable = [
       'camps_count',
        'contributors_count',
        'projects_count',
        'families_count',
    ];

    public function slides()
    {
        return $this->hasMany(HomepageSlide::class);
    }

 
}