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
            ->where(function ($query) {
                $query->whereNull('threshold_value')
                    ->orWhere('threshold_value', '<', 2);
            })
            ->update([
                'threshold_value' => 2,
                'updated_at' => now(),
            ]);
    }

    public function down(): void
    {
        // Do not restore an invalid threshold or overwrite an administrator's setting.
    }
};
