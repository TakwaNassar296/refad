<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class FamilyMember extends Model
{
    use HasFactory, SoftDeletes ,  LogsActivity;

    protected $fillable = [
        'family_id',
        'name',
        'gender',
        'dob',
        'national_id',
        'relationship_id',
        'medical_condition_id',
        'file',
    ];

    protected $casts = [
        'dob' => 'date',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'family_id',
                'name',
                'gender',
                'dob',
                'national_id'
            ])
            ->logOnlyDirty()
            ->setDescriptionForEvent(function (string $eventName) {
                $eventText = match($eventName) {
                    'created' => 'تم إضافة فرد جديد للعائلة',
                    'updated' => 'تم تحديث بيانات فرد من العائلة',
                    'deleted' => 'تم حذف فرد من العائلة',
                    default => $eventName,
                };

                return $eventText . ': ' . $this->family->family_name;
            });
    }


    public function family(): BelongsTo
    {
        return $this->belongsTo(Family::class);
    }

    public function relationship()
    {
        return $this->belongsTo(Relationship::class);
    }

    public function medicalCondition()
    {
        return $this->belongsTo(MedicalCondition::class);
    }
}