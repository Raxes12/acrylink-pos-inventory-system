<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SecurityRule extends Model
{
    private const NO_THRESHOLD = ['off_hours_transaction', 'session_ip_mismatch', 'user_agent_anomaly'];
    private const NO_WINDOW = ['off_hours_transaction', 'large_transaction', 'large_transaction_amount', 'excessive_discount', 'bulk_inventory_change', 'large_report_date_range'];
    private const INTEGER_THRESHOLD = [
        'failed_login_limit', 'ip_rate_limit', 'rogue_device', 'duplicate_session',
        'duplicate_session_detection', 'rapid_successive_transaction', 'excessive_void',
        'bulk_inventory_change', 'inventory_discrepancy_spike',
        'unauthorized_endpoint_access', 'bulk_data_export', 'report_access_limit',
        'large_report_date_range', 'sql_injection_attempt', 'xss_attempt',
    ];

    protected $primaryKey = 'security_rule_id';

    protected $fillable = [
        'rule_name',
        'rule_type',
        'threshold_value',
        'time_window_minutes',
        'severity',
        'action',
        'is_active',
    ];

    protected $casts = [
        'threshold_value' => 'decimal:2',
        'time_window_minutes' => 'integer',
        'is_active' => 'boolean',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function intrusionEvents()
    {
        return $this->hasMany(IntrusionEvent::class, 'rule_id', 'security_rule_id');
    }

    public static function activeRule(string $name): ?self
    {
        return static::active()->where('rule_name', $name)->first();
    }

    public function thresholdOr(float|int $fallback): float|int
    {
        return $this->threshold_value !== null ? (float) $this->threshold_value : $fallback;
    }

    public function windowOr(int $fallback): int
    {
        return $this->time_window_minutes ?: $fallback;
    }

    public function usesThreshold(): bool
    {
        return !in_array($this->rule_name, self::NO_THRESHOLD, true);
    }

    public function usesWindow(): bool
    {
        return !in_array($this->rule_name, self::NO_WINDOW, true);
    }

    public function integerThreshold(): bool
    {
        return in_array($this->rule_name, self::INTEGER_THRESHOLD, true);
    }

    public function minimumThreshold(): int
    {
        return in_array($this->rule_name, ['duplicate_session', 'duplicate_session_detection'], true) ? 2 : 1;
    }

    public function supportedActions(): array
    {
        // Inventory movements currently record anomalies after the stock write.
        // Blocking here would roll back the event along with the movement.
        return in_array($this->rule_name, ['bulk_inventory_change', 'inventory_discrepancy_spike'], true)
            ? ['log', 'alert', 'notify']
            : ['log', 'alert', 'block', 'notify'];
    }
}
