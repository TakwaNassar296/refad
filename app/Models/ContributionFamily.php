<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContributionFamily extends Model
{
    use HasFactory;

    protected $table = 'contribution_families';

    protected $fillable = [
        'contribution_id',
        'family_id',
    ];

    public function contribution()
    {
        return $this->belongsTo(Contribution::class);
    }

    public function family()
    {
        return $this->belongsTo(Family::class);
    }
}
