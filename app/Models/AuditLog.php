<?php

namespace App\Models;

use App\Services\AuditLogService;
use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'username',
        'role',
        'action',
        'detail',
        'ip_address',
        'mac_address',
        'user_agent',
        'affected_table',
        'affected_record_id',
        'old_values',
        'new_values',
        'timestamp',
    ];

    protected $casts = [
        'timestamp' => 'datetime',
        'old_values' => 'array',
        'new_values' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public static function record(string $action, string $detail, ?User $user = null, array $context = []): void
    {
        app(AuditLogService::class)->record($action, $detail, $user, $context);
    }
}
