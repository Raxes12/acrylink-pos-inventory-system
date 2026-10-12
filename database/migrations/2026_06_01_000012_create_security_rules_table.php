<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('security_rules', function (Blueprint $table) {
            $table->id('security_rule_id');
            $table->string('rule_name', 100)->unique();
            $table->string('rule_type', 50);
            $table->decimal('threshold_value', 12, 2)->nullable();
            $table->unsignedInteger('time_window_minutes')->nullable();
            $table->enum('severity', ['low', 'medium', 'high', 'critical'])->default('medium');
            $table->enum('action', ['log', 'alert', 'block', 'notify'])->default('alert');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['rule_type', 'is_active']);
            $table->index(['severity', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('security_rules');
    }
};
