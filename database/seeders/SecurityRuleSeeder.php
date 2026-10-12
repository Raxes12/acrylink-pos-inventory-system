<?php

namespace Database\Seeders;

use App\Models\SecurityRule;
use Illuminate\Database\Seeder;

class SecurityRuleSeeder extends Seeder
{
    public function run(): void
    {
        $rules = [
            ['failed_login_limit', 'authentication', 3, 10, 'high', 'alert'],
            ['ip_rate_limit', 'network', 60, 1, 'medium', 'alert'],
            ['rogue_device', 'access_control', 3, 10, 'critical', 'block'],
            ['duplicate_session', 'session', 2, 5, 'medium', 'block'],
            ['off_hours_transaction', 'transaction', null, null, 'medium', 'alert'],
            ['large_transaction', 'transaction', 10000, null, 'high', 'alert'],
            ['rapid_successive_transaction', 'transaction', 5, 2, 'medium', 'alert'],
            ['excessive_discount', 'transaction', 20, null, 'medium', 'alert'],
            ['excessive_void', 'transaction', 3, 60, 'high', 'alert'],
            ['bulk_inventory_change', 'inventory', 50, null, 'high', 'alert'],
            ['inventory_discrepancy_spike', 'inventory', 5, 60, 'high', 'alert'],
            ['unauthorized_endpoint_access', 'access_control', 1, 5, 'critical', 'block'],
            ['bulk_data_export', 'access_control', 3, 10, 'high', 'notify'],
            ['report_access_limit', 'access_control', 10, 10, 'medium', 'alert'],
            ['large_report_date_range', 'access_control', 31, null, 'high', 'notify'],
            ['session_ip_mismatch', 'session', null, 5, 'high', 'alert'],
            ['user_agent_anomaly', 'session', null, 5, 'medium', 'alert'],
            ['sql_injection_attempt', 'input_validation', 1, 5, 'critical', 'block'],
            ['xss_attempt', 'input_validation', 1, 5, 'high', 'block'],
        ];

        foreach ($rules as [$name, $type, $threshold, $window, $severity, $action]) {
            SecurityRule::firstOrCreate(
                ['rule_name' => $name],
                [
                    'rule_type' => $type,
                    'threshold_value' => $threshold,
                    'time_window_minutes' => $window,
                    'severity' => $severity,
                    'action' => $action,
                    'is_active' => true,
                ]
            );
        }
    }
}
