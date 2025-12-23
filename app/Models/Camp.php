<?php

namespace App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;


class Camp extends Model
{
    use HasFactory, SoftDeletes, HasTranslations , LogsActivity;

    public $translatable = ['name' , 'description']; 

    protected $fillable = [
        'name',
        'family_count',
        'children_count',
        'elderly_count',
        'latitude',
        'longitude',
        'bank_account',
        'description',
        'camp_img',   
        'location', 
        'governorate_id'
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'name',
                'description',
                'family_count',
                'children_count',
                'elderly_count',
                'latitude',
                'longitude',
                'bank_account',
                'slug'
            ])
            ->logOnlyDirty()
            ->setDescriptionForEvent(function (string $eventName) {
            $eventText = match($eventName) {
                'created' => 'تم إنشاء',
                'updated' => 'تم تحديث',
                'deleted' => 'تم حذف',
                default => $eventName,
            };
            return "{$eventText} المخيم «{$this->getTranslation('name', 'ar')}»";
        });
    }


    public function delegates()
    {
        return $this->hasMany(User::class);
    }


    public function families()
    {
        return $this->hasMany(Family::class);
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($camp) {
            if (empty($camp->slug)) {
                $name = is_array($camp->name)
                    ? ($camp->name['ar'] ?? $camp->name['en'])
                    : $camp->name;

                $camp->slug = static::generateUniqueSlug($name);
            }
        });

        static::updating(function ($camp) {
            $originalName = $camp->getOriginal('name');

            $original = is_array($originalName)
                ? ($originalName['ar'] ?? $originalName['en'])
                : $originalName;

            $current = is_array($camp->name)
                ? ($camp->name['ar'] ?? $camp->name['en'])
                : $camp->name;

            if ($original !== $current) {
                $camp->slug = static::generateUniqueSlug($current, $camp->id);
            }
        });
    }


    public static function generateUniqueSlug(string $name, $ignoreId = null, string $separator = '-'): string
    {
        $slug = trim($name);

        $slug = preg_replace('/[^\p{Arabic}A-Za-z0-9]+/u', $separator, $slug);
        $slug = trim($slug, $separator);

        $original = $slug;
        $count = 1;

        while (
            static::withTrashed()
                ->where('slug', $slug)
                ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
                ->exists()
        ) {
            $slug = "{$original}{$separator}{$count}";
            $count++;
        }

        return $slug;
    }



    public function projects()
    {
        return $this->hasMany(Project::class);
    }

    public function complaints()
    {
        return $this->hasMany(Complaint::class);
    }

    public function governorate()
    {
        return $this->belongsTo(Governorate::class);
    }

}
