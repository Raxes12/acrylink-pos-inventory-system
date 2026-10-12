<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserSession extends Model
{
    protected $fillable = [
        'session_id',
        'user_id',
        'ip_address',
        'mac_address',
        'user_agent',
        'login_time',
        'last_activity',
        'logout_time',
        'session_status',
    ];

    protected $casts = [
        'login_time' => 'datetime',
        'last_activity' => 'datetime',
        'logout_time' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
