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
    use HasFactory,LogsActivity;

    protected $fillable = [
        'family_id',
        'name',
        'gender',
        'dob',
        'national_id',
        'relationship_id',
        'medical_condition_id',
        'other_medical_condition',
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


    public function getAgeGroupAttribute(): ?string
    {
        if (!$this->dob) {
            return null;
        }

        $days  = $this->dob->diffInDays(now());
        $years = $this->dob->diffInYears(now());

        return match (true) {
            $days <= 28                => 'newborns',
            $days === 29               => 'infants',
            $years >= 1  && $years < 2 => 'veryEarlyChildhood',
            $years >= 2  && $years < 3 => 'toddlers',
            $years >= 3  && $years < 5 => 'earlyChildhood',
            $years >= 5  && $years < 10=> 'children',
            $years >= 10 && $years < 18=> 'adolescents',
            $years >= 18 && $years < 25=> 'youth',
            $years >= 25 && $years < 40=> 'youngAdults',
            $years >= 40 && $years < 50=> 'middleAgeAdults',
            $years >= 50 && $years < 60=> 'lateMiddleAge',
            $years >= 60               => 'seniors',
            default                    => null,
        };
    }

}