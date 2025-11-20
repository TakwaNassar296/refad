<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Contribution extends Model
{
    use HasFactory , LogsActivity;

    protected $fillable = [
        'user_id',
        'project_id',
        'total_quantity',
        'notes',
        'status',
        'delegate_id'
    ];

    public function getActivitylogOptions(): \Spatie\Activitylog\LogOptions
    {
        return \Spatie\Activitylog\LogOptions::defaults()
            ->logAll()
            ->useLogName('contributions')
            ->setDescriptionForEvent(function (string $eventName) {

                $eventText = match ($eventName) {
                    'created' => 'تم إنشاء المساهمة',
                    'updated' => 'تم تحديث المساهمة',
                    'deleted' => 'تم حذف المساهمة',
                    default   => $eventName,
                };
                $projectName = $this->project?->name ?? 'بدون مشروع';

                return $eventText . ' في المشروع "' . $projectName . '"';
            });
    }


    public function contributor()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function delegate()
    {
        return $this->belongsTo(User::class, 'delegate_id');
    }   

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function families()
    {
        return $this->belongsToMany(Family::class, 'contribution_families')
                    ->withTimestamps();
    }
}
