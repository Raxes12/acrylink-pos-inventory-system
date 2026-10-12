<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    private const INTRUSION_EVENT_TYPES = [
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
        // Authentication and access monitoring
        $this->createLoginAttemptsTable();
        $this->createAuthorizedDevicesTable();
        $this->createIntrusionEventsTable();
        $this->createUserSessionsTable();
    }

    public function down(): void
    {
        Schema::dropIfExists('user_sessions');
        Schema::dropIfExists('intrusion_events');
        Schema::dropIfExists('authorized_devices');
        Schema::dropIfExists('login_attempts');
    }

    private function createLoginAttemptsTable(): void
    {
        Schema::create('login_attempts', function (Blueprint $table) {
            $table->id();
            $table->string('username', 50);
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('ip_address', 45)->nullable();
            $table->string('mac_address', 17)->nullable();
            $table->enum('attempt_status', ['success', 'failed']);
            $table->string('failure_reason', 150)->nullable();
            $table->dateTime('attempted_at')->useCurrent();

            $table->index(['username', 'attempt_status', 'attempted_at']);
            $table->index(['ip_address', 'attempt_status', 'attempted_at']);
        });
    }

    private function createAuthorizedDevicesTable(): void
    {
        Schema::create('authorized_devices', function (Blueprint $table) {
            $table->id();
            $table->string('device_name', 100);
            $table->string('mac_address', 17)->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->foreignId('assigned_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('status', ['authorized', 'blocked', 'inactive'])->default('authorized');
            $table->timestamps();

            $table->index(['ip_address', 'status']);
            $table->index(['mac_address', 'status']);
        });
    }

    private function createIntrusionEventsTable(): void
    {
        Schema::create('intrusion_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->enum('event_type', self::INTRUSION_EVENT_TYPES);
            $table->string('source_module', 50)->nullable();
            $table->string('request_path', 2048)->nullable();
            $table->string('request_method', 10)->nullable();
            $table->string('user_role', 100)->nullable();
            $table->string('required_roles', 255)->nullable();
            $table->enum('severity', ['low', 'medium', 'high', 'critical'])->default('medium');
            $table->text('description');
            $table->string('ip_address', 45)->nullable();
            $table->string('mac_address', 17)->nullable();
            $table->text('user_agent')->nullable();
            $table->enum('event_status', ['open', 'reviewed', 'resolved'])->default('open');
            $table->dateTime('detected_at')->useCurrent();
            $table->dateTime('resolved_at')->nullable();
            $table->timestamps();

            $table->index(['event_type', 'event_status', 'detected_at']);
            $table->index(['severity', 'event_status']);
            $table->index(['event_type', 'user_id', 'request_method', 'detected_at'], 'intrusion_events_endpoint_access_index');
        });
    }

    private function createUserSessionsTable(): void
    {
        // Acry-Link security ledger for session monitoring. This is not Laravel's
        // session payload table; see create_sessions_table.php for that table.
        Schema::create('user_sessions', function (Blueprint $table) {
            $table->id();
            $table->string('session_id')->unique();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('ip_address', 45)->nullable();
            $table->string('mac_address', 17)->nullable();
            $table->text('user_agent')->nullable();
            $table->dateTime('login_time');
            $table->dateTime('last_activity');
            $table->dateTime('logout_time')->nullable();
            $table->enum('session_status', ['active', 'expired', 'logged_out'])->default('active');
            $table->timestamps();

            $table->index(['user_id', 'session_status']);
        });
    }
};
