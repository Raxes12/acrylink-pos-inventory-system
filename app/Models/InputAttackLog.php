<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InputAttackLog extends Model
{
    protected $primaryKey = 'input_attack_log_id';

    public const UPDATED_AT = null;

    protected $fillable = [
        'user_id',
        'attack_type',
        'request_url',
        'request_method',
        'input_payload',
        'ip_address',
        'user_agent',
        'severity',
        'detected_at',
    ];

    protected $casts = [
        'detected_at' => 'datetime',
        'created_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
