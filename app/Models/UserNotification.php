<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserNotification extends Model
{


    protected $fillable = [
        'type',
        'notifiable_type',
        'notifiable_id', 
        'notification_title', 
        'data',
        'read_at'
    ];

    protected $casts = [
        'read_at' => 'datetime'
    ];

   

    public function notifiable()
    {
        return $this->morphTo();
    }

    public function markAsRead()
    {
        $this->update(['read_at' => now()]);
    }
}