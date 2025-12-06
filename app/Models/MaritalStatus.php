<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MaritalStatus extends Model
{
    use HasFactory, SoftDeletes;
    
    protected $fillable = ['name'];

    public function families()
    {
        return $this->hasMany(Family::class);
    }
}
