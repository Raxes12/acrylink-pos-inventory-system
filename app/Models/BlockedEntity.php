<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BlockedEntity extends Model
{
    public const TYPE_USER = 'user';
    public const TYPE_IP_ADDRESS = 'ip_address';
    public const TYPE_MAC_ADDRESS = 'mac_address';
    public const TYPE_DEVICE = 'device';

    public const STATUS_ACTIVE = 'active';
    public const STATUS_EXPIRED = 'expired';
    public const STATUS_REMOVED = 'removed';

    protected $primaryKey = 'blocked_entity_id';

    public $timestamps = false;

    protected $fillable = [
        'entity_type',
        'entity_value',
        'reason',
        'blocked_by',
        'blocked_at',
        'expires_at',
        'status',
    ];

    protected $casts = [
        'blocked_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE)
            ->where(function ($query) {
                $query->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            });
    }

    public function blocker()
    {
        return $this->belongsTo(User::class, 'blocked_by');
    }

    public function isExpired(): bool
    {
        return $this->expires_at !== null && $this->expires_at->lte(now());
    }
}
