<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        DB::statement("ALTER TABLE intrusion_events MODIFY event_type ENUM(
            'failed_login','ip_rate_limit','duplicate_session','rogue_device',
            'unauthorized_endpoint_access','bulk_data_export','report_access_limit',
            'large_report_date_range','off_hours_transaction','large_transaction',
            'rapid_successive_transaction','excessive_discount','excessive_void',
            'bulk_inventory_change','inventory_discrepancy_spike','session_ip_mismatch',
            'user_agent_anomaly','sql_injection_attempt','xss_attempt','abnormal_activity'
        ) NOT NULL");
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        // Preserve records that use the new types during rollback.
        DB::table('intrusion_events')->where('event_type', 'report_access_limit')->update(['event_type' => 'bulk_data_export']);
        DB::table('intrusion_events')->where('event_type', 'large_report_date_range')->update(['event_type' => 'bulk_data_export']);
        DB::statement("ALTER TABLE intrusion_events MODIFY event_type ENUM(
            'failed_login','ip_rate_limit','duplicate_session','rogue_device',
            'unauthorized_endpoint_access','bulk_data_export','off_hours_transaction',
            'large_transaction','rapid_successive_transaction','excessive_discount',
            'excessive_void','bulk_inventory_change','inventory_discrepancy_spike',
            'session_ip_mismatch','user_agent_anomaly','sql_injection_attempt',
            'xss_attempt','abnormal_activity'
        ) NOT NULL");
    }
};
