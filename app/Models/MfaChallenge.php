<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MfaChallenge extends Model
{
    public const STATUS_PENDING = 'pending';
    public const STATUS_VERIFIED = 'verified';
    public const STATUS_EXPIRED = 'expired';
    public const STATUS_FAILED = 'failed';

    public const METHOD_EMAIL = 'email';

    protected $primaryKey = 'mfa_challenge_id';

    public const UPDATED_AT = null;

    protected $fillable = [
        'user_id',
        'otp_hash',
        'delivery_method',
        'expires_at',
        'verified_at',
        'attempt_count',
        'status',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'verified_at' => 'datetime',
        'created_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function isExpired(): bool
    {
        return now()->greaterThanOrEqualTo($this->expires_at);
    }
}
