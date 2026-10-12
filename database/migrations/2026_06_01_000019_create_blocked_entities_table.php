<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('blocked_entities', function (Blueprint $table) {
            $table->id('blocked_entity_id');
            $table->enum('entity_type', ['user', 'ip_address', 'mac_address', 'device']);
            $table->string('entity_value', 255);
            $table->text('reason');
            $table->foreignId('blocked_by')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('blocked_at')->useCurrent();
            $table->dateTime('expires_at')->nullable();
            $table->enum('status', ['active', 'expired', 'removed'])->default('active');

            $table->index(['entity_type', 'entity_value', 'status'], 'blocked_entities_lookup_index');
            $table->index(['status', 'expires_at'], 'blocked_entities_status_expires_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('blocked_entities');
    }
};
