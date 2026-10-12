<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuthorizedDevice extends Model
{
    protected $fillable = [
        'device_name',
        'mac_address',
        'ip_address',
        'assigned_user_id',
        'status',
    ];

    public function assignedUser()
    {
        return $this->belongsTo(User::class, 'assigned_user_id');
    }
}
