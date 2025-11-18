<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Family extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'camp_id',
        'added_by',
        'family_name',
        'father_name',
        'national_id',
        'dob',
        'phone',
        'email',
        'elderly_count',
        'medical_conditions_count',
        'children_count',
        'tent_number',
        'location',
        'notes',
        'total_members'
    ];

    protected $casts = [
        'dob' => 'date',
    ];

    public function camp(): BelongsTo
    {
        return $this->belongsTo(Camp::class);
    }

    public function delegate(): BelongsTo
    {
        return $this->belongsTo(User::class, 'added_by');
    }

    public function members(): HasMany
    {
        return $this->hasMany(FamilyMember::class);
    }

    public function supportedProjects()
    {
        return $this->belongsToMany(Project::class, 'project_beneficiaries')
                    ->withPivot(['support_date', 'notes'])
                    ->withTimestamps();
    }
}