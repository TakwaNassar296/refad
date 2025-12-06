<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MedicalCondition extends Model
{
    use HasFactory , SoftDeletes;
    
    protected $fillable = ['name'];

    public function familyMembers()
    {
        return $this->hasMany(FamilyMember::class);
    }
}
