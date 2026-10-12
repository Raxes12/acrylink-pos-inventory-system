<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->dateTime('timestamp')->useCurrent();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('username', 50);
            $table->string('role', 20);
            $table->string('action', 30);
            $table->text('detail');
            $table->string('ip_address', 45);
            $table->string('mac_address', 17)->nullable();
            $table->text('user_agent')->nullable();
            $table->string('affected_table', 100)->nullable();
            $table->string('affected_record_id', 100)->nullable();
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();

            $table->index('timestamp');
            $table->index('username');
            $table->index('action');
            $table->index(['action', 'timestamp']);
            $table->index(['affected_table', 'affected_record_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
