<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Complaint extends Model
{
    use HasFactory , SoftDeletes;

    protected $fillable = [
        'name',
        'phone',
        'email',
        'camp_id',
        'topic',
        'message',
    ];


    public function camp()
    {
        return $this->belongsTo(Camp::class);
    }
}