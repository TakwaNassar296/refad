<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Project extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'camp_id',
        'added_by',
        'name',
        'type',
        'beneficiary_count',
        'college',
        'project_number',
        'status',
        'notes',
        'file_path',
        'file_original_name',
        'file_type',
        'file_size'
    ];

    public function camp(): BelongsTo
    {
        return $this->belongsTo(Camp::class);
    }

    public function delegate(): BelongsTo
    {
        return $this->belongsTo(User::class, 'added_by');
    }

    public function beneficiaryFamilies(): BelongsToMany
    {
        return $this->belongsToMany(Family::class, 'project_beneficiaries')
                    ->withPivot(['support_date', 'notes'])
                    ->withTimestamps();
    }
    
    public function updateBeneficiaryCount(): void
    {
        $this->update([
            'beneficiary_count' => $this->beneficiaryFamilies()->count()
        ]);
    }
}