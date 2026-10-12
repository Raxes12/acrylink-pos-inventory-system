<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('security_rules')) {
            return;
        }

        DB::table('security_rules')
            ->whereIn('rule_name', ['duplicate_session', 'duplicate_session_detection'])
            ->where('action', 'alert')
            ->update([
                'action' => 'block',
                'updated_at' => now(),
            ]);
    }

    public function down(): void
    {
        if (!Schema::hasTable('security_rules')) {
            return;
        }

        DB::table('security_rules')
            ->whereIn('rule_name', ['duplicate_session', 'duplicate_session_detection'])
            ->where('action', 'block')
            ->update([
                'action' => 'alert',
                'updated_at' => now(),
            ]);
    }
};
