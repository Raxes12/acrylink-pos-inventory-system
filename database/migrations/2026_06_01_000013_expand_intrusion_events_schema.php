<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /*
     * This migration updates intrusion_events so the IDS can store all event
     * types required by the research paper, plus the rule/source context used
     * by the dashboard and alert system.
     */
    private const EVENT_TYPES = [
        'failed_login',
        'ip_rate_limit',
        'duplicate_session',
        'rogue_device',
        'unauthorized_endpoint_access',
        'bulk_data_export',
        'off_hours_transaction',
        'large_transaction',
        'rapid_successive_transaction',
        'excessive_discount',
        'excessive_void',
        'bulk_inventory_change',
        'inventory_discrepancy_spike',
        'session_ip_mismatch',
        'user_agent_anomaly',
        'sql_injection_attempt',
        'xss_attempt',
        'abnormal_activity',
    ];

    public function up(): void
    {
        $this->expandEventTypeEnum();
        $this->addRuleAndSourceContext();
        $this->addReportingIndexes();
    }

    public function down(): void
    {
        Schema::table('intrusion_events', function (Blueprint $table) {
            if ($this->indexExists('intrusion_events_source_status_detected_index')) {
                $table->dropIndex('intrusion_events_source_status_detected_index');
            }
            if ($this->indexExists('intrusion_events_rule_status_index')) {
                $table->dropIndex('intrusion_events_rule_status_index');
            }

            if (Schema::hasColumn('intrusion_events', 'rule_id')) {
                $table->dropForeign(['rule_id']);
                $table->dropColumn('rule_id');
            }
        });
    }

    private function supportsEnumAlter(): bool
    {
        return in_array(DB::getDriverName(), ['mysql', 'mariadb'], true);
    }

    private function expandEventTypeEnum(): void
    {
        if (!$this->supportsEnumAlter()) {
            return;
        }

        $types = collect(self::EVENT_TYPES)
            ->map(fn ($type) => "'{$type}'")
            ->implode(',');

        DB::statement("ALTER TABLE intrusion_events MODIFY event_type ENUM({$types}) NOT NULL");
    }

    private function addRuleAndSourceContext(): void
    {
        Schema::table('intrusion_events', function (Blueprint $table) {
            if (!Schema::hasColumn('intrusion_events', 'rule_id')) {
                $table->unsignedBigInteger('rule_id')->nullable()->after('user_id');
                $table->foreign('rule_id')
                    ->references('security_rule_id')
                    ->on('security_rules')
                    ->nullOnDelete();
            }

            if (!Schema::hasColumn('intrusion_events', 'source_module')) {
                $table->string('source_module', 50)->nullable()->after('event_type');
            }

            if (!Schema::hasColumn('intrusion_events', 'request_path')) {
                $table->string('request_path', 2048)->nullable()->after('source_module');
            }

            if (!Schema::hasColumn('intrusion_events', 'request_method')) {
                $table->string('request_method', 10)->nullable()->after('request_path');
            }

            if (!Schema::hasColumn('intrusion_events', 'user_role')) {
                $table->string('user_role', 100)->nullable()->after('request_method');
            }

            if (!Schema::hasColumn('intrusion_events', 'required_roles')) {
                $table->string('required_roles', 255)->nullable()->after('user_role');
            }

            if (!Schema::hasColumn('intrusion_events', 'user_agent')) {
                $table->text('user_agent')->nullable()->after('mac_address');
            }
        });
    }

    private function addReportingIndexes(): void
    {
        Schema::table('intrusion_events', function (Blueprint $table) {
            if (!$this->indexExists('intrusion_events_source_status_detected_index')) {
                $table->index(['source_module', 'event_status', 'detected_at'], 'intrusion_events_source_status_detected_index');
            }

            if (!$this->indexExists('intrusion_events_rule_status_index')) {
                $table->index(['rule_id', 'event_status'], 'intrusion_events_rule_status_index');
            }
        });
    }

    private function indexExists(string $indexName): bool
    {
        return collect(Schema::getIndexes('intrusion_events'))
            ->contains(fn (array $index) => ($index['name'] ?? null) === $indexName);
    }
};
