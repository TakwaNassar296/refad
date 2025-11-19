<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Project extends Model
{
    use HasFactory, SoftDeletes , LogsActivity;

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
        'file_size',
        'total_received',       
        'total_remaining',      
        'total_contributions',  
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
                    ->withPivot(['requested_quantity', 'received_quantity', 'received', 'notes', 'support_date'])
                    ->withTimestamps();
    }

    public function contributions()
    {
        return $this->hasMany(Contribution::class, 'project_id');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'camp_id',
                'added_by',
                'name',
                'type',
                'beneficiary_count',
                'college',
                'project_number',
                'status',
                'notes',
                'total_received',
                'total_remaining',
                'total_contributions',
            ])
            ->logOnlyDirty()
            ->setDescriptionForEvent(function (string $eventName) {
                $eventText = match($eventName) {
                    'created' => 'تم إنشاء المشروع',
                    'updated' => 'تم تحديث المشروع',
                    'deleted' => 'تم حذف المشروع',
                    default => $eventName,
                };
                return $eventText . ': ' . $this->name;
            });
    }


    
  
}