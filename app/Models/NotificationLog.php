<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NotificationLog extends Model
{
    protected $primaryKey = 'notification_log_id';

    public const UPDATED_AT = null;

    protected $fillable = [
        'intrusion_alert_id',
        'recipient_email',
        'subject',
        'message',
        'notification_type',
        'send_status',
        'sent_at',
        'error_message',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
        'created_at' => 'datetime',
    ];

    public function intrusionAlert()
    {
        return $this->belongsTo(IntrusionAlert::class, 'intrusion_alert_id', 'intrusion_alert_id');
    }
}
