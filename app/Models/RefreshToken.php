<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\SoftDeletes;

class RefreshToken extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'token',
        'expires_at',
        'is_revoked',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'is_revoked' => 'boolean',
    ];

    public static function createToken(User $user): string
    {
        $plainToken = Str::random(64);
        self::create([
            'user_id' => $user->id,
            'token' => hash('sha256', $plainToken),
            'expires_at' => now()->addDays(30),
            'is_revoked' => false,
        ]);

        return $plainToken;
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
