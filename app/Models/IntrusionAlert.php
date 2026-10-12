<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IntrusionAlert extends Model
{
    protected $primaryKey = 'intrusion_alert_id';

    public const UPDATED_AT = null;

    protected $fillable = [
        'intrusion_event_id',
        'alert_title',
        'alert_message',
        'severity',
        'alert_channel',
        'alert_status',
        'acknowledged_by',
        'acknowledged_at',
        'resolved_by',
        'resolved_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'acknowledged_at' => 'datetime',
        'resolved_at' => 'datetime',
    ];

    public function intrusionEvent()
    {
        return $this->belongsTo(IntrusionEvent::class);
    }

    public function notificationLogs()
    {
        return $this->hasMany(NotificationLog::class, 'intrusion_alert_id', 'intrusion_alert_id');
    }

    public function latestNotificationLog()
    {
        return $this->hasOne(NotificationLog::class, 'intrusion_alert_id', 'intrusion_alert_id')->latestOfMany('created_at');
    }

    public function acknowledgedBy()
    {
        return $this->belongsTo(User::class, 'acknowledged_by');
    }

    public function resolvedBy()
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }
}
