<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdminPosition extends Model
{
    protected $fillable = ['name'];

    public function users()
    {
        return $this->hasMany(User::class, 'admin_position_id');
    }
}
