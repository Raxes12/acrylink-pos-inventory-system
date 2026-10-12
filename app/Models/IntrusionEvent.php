<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IntrusionEvent extends Model
{
    protected $fillable = [
        'user_id',
        'rule_id',
        'event_type',
        'source_module',
        'request_path',
        'request_method',
        'user_role',
        'required_roles',
        'severity',
        'description',
        'ip_address',
        'mac_address',
        'user_agent',
        'event_status',
        'detected_at',
        'resolved_at',
    ];

    protected $casts = [
        'detected_at' => 'datetime',
        'resolved_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function rule()
    {
        return $this->belongsTo(SecurityRule::class, 'rule_id', 'security_rule_id');
    }

    public function alerts()
    {
        return $this->hasMany(IntrusionAlert::class, 'intrusion_event_id', 'id');
    }

    public function latestAlert()
    {
        return $this->hasOne(IntrusionAlert::class, 'intrusion_event_id', 'id')->latestOfMany('created_at');
    }
}
