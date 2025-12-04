<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Family extends Model
{
    use HasFactory, SoftDeletes , LogsActivity;

    protected $fillable = [
        'camp_id',
        'added_by',
        'family_name',
        'national_id',
        'dob',
        'phone',
        'elderly_count',
        'medical_conditions_count',
        'children_count',
        'tent_number',
        'location',
        'notes',
        'total_members',
        'file'
    ];

    protected $casts = [
        'dob' => 'date',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->useLogName('families')
            ->setDescriptionForEvent(function (string $eventName) {

                $eventText = match ($eventName) {
                    'created' => 'تم إنشاء العائلة',
                    'updated' => 'تم تحديث العائلة',
                    'deleted' => 'تم حذف العائلة',
                    default   => $eventName,
                };

                $familyName = $this->family_name ?? 'بدون اسم';
                $campName   = $this->camp?->name ?? 'بدون مخيم';

               return "{$eventText} '{$familyName}' في المخيم '{$campName}'";
            })
            ->logOnlyDirty();
    }

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

    public function contributions()
    {
        return $this->belongsToMany(Contribution::class, 'contribution_families')
                    ->withTimestamps();
    }

    public function delegateContributions()
    {
        return $this->belongsToMany(Contribution::class, 'delegate_families')
                    ->withPivot('received_quantity', 'notes')
                    ->withTimestamps();
    }


}