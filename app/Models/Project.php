<?php

namespace App\Models;

use App\Models\Contribution;
use Spatie\Activitylog\LogOptions;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

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
        'status',
        'notes',
        'total_received',       
        'total_remaining',      
        'total_contributions', 
        'is_approved',
        'project_image' 
    ];


    protected $casts = [
        'is_approved' => 'boolean', 
        'total_received' => 'integer',
        'total_remaining' => 'integer',
    ];

    public function camp(): BelongsTo
    {
        return $this->belongsTo(Camp::class);
    }

    public function addedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'added_by');
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