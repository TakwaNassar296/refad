<?php

namespace App\Models;

use App\Models\Camp;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable , SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'id_number',
        'phone',
        'role',
        'license_number',
        'accept_terms',
        'status',
        'admin_position',
        'reset_code',
        'reset_code_expires_at',
        'backup_phone',
        'camp_id'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'accept_terms' => 'boolean',
            'reset_code_expires_at' => 'datetime',
        ];
    }

    public function refreshTokens()
    {
        return $this->hasMany(RefreshToken::class);
    }

    public function generateResetCode(): void
    {
        $this->reset_code = rand(100000, 999999); 
        $this->reset_code_expires_at = now()->addMinutes(15);
        $this->save();
    }

    public function isValidResetCode(string $code): bool
    {
        return $this->reset_code === $code && $this->reset_code_expires_at?->isFuture();
    }

    public function clearResetCode(): void
    {
        $this->reset_code = null;
        $this->reset_code_expires_at = null;
        $this->save();
    }

    public function camp()
    {
        return $this->belongsTo(Camp::class);
    }


    public function isDelegate(): bool
    {
        return $this->role === 'delegate';
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

}
