<?php

namespace App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Camp extends Model
{
    use HasFactory, SoftDeletes, HasTranslations;

    public $translatable = ['name']; 

    protected $fillable = [
        'name',
        'family_count',
        'children_count',
        'elderly_count',
        'latitude',
        'longitude',
        'bank_account',
        'slug'
    ];

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
                $camp->slug = static::generateUniqueSlug($camp->name);
            }
        });

        static::updating(function ($camp) {
            $originalName = $camp->getOriginal('name');
            $original = is_array($originalName) ? ($originalName['en'] ?? $originalName['ar'] ?? null) : $originalName;
            $current = is_array($camp->name) ? ($camp->name['en'] ?? $camp->name['ar'] ?? null) : $camp->name;

            if ($original !== $current) {
                $camp->slug = static::generateUniqueSlug($camp->name);
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

        while (static::where('slug', $slug)
            ->when($ignoreId, fn($q) => $q->where('id', '!=', $ignoreId))
            ->exists()) {
            $slug = "{$original}{$separator}{$count}";
            $count++;
        }

        return $slug;
    }

    public function projects()
    {
        return $this->hasMany(Project::class);
    }

}
