<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        $this->createInputAttackLogsTable();
        $this->createIntrusionAlertsTable();
        $this->createNotificationLogsTable();
    }

    public function down(): void
    {
        Schema::dropIfExists('notification_logs');
        Schema::dropIfExists('intrusion_alerts');
        Schema::dropIfExists('input_attack_logs');
    }

    private function createInputAttackLogsTable(): void
    {
        Schema::create('input_attack_logs', function (Blueprint $table) {
            $table->id('input_attack_log_id');
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->enum('attack_type', ['sql_injection', 'xss', 'command_injection', 'other']);
            $table->string('request_url', 2048);
            $table->string('request_method', 10);
            $table->text('input_payload');
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->enum('severity', ['low', 'medium', 'high', 'critical'])->default('high');
            $table->dateTime('detected_at')->useCurrent();
            $table->timestamp('created_at')->nullable();

            $table->index(['attack_type', 'detected_at']);
            $table->index(['severity', 'detected_at']);
            $table->index('user_id');
        });
    }

    private function createIntrusionAlertsTable(): void
    {
        Schema::create('intrusion_alerts', function (Blueprint $table) {
            $table->id('intrusion_alert_id');
            $table->foreignId('intrusion_event_id')->constrained('intrusion_events')->cascadeOnDelete();
            $table->string('alert_title', 150);
            $table->text('alert_message');
            $table->enum('severity', ['low', 'medium', 'high', 'critical']);
            $table->enum('alert_channel', ['dashboard', 'email', 'both'])->default('both');
            $table->enum('alert_status', ['new', 'acknowledged', 'resolved'])->default('new');
            $table->foreignId('acknowledged_by')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('acknowledged_at')->nullable();
            $table->foreignId('resolved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('resolved_at')->nullable();
            $table->timestamp('created_at')->nullable();

            $table->index(['severity', 'alert_status']);
            $table->index('created_at');
        });
    }

    private function createNotificationLogsTable(): void
    {
        Schema::create('notification_logs', function (Blueprint $table) {
            $table->id('notification_log_id');
            $table->foreignId('intrusion_alert_id')
                ->constrained('intrusion_alerts', 'intrusion_alert_id')
                ->cascadeOnDelete();
            $table->string('recipient_email', 150);
            $table->string('subject', 150);
            $table->text('message');
            $table->string('notification_type', 50);
            $table->enum('send_status', ['pending', 'sent', 'failed'])->default('pending');
            $table->dateTime('sent_at')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamp('created_at')->nullable();

            $table->index(['send_status', 'created_at']);
            $table->index('recipient_email');
        });
    }
};
