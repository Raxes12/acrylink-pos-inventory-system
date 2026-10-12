<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // These rules inspect movements after the stock write, so they cannot
        // safely promise a blocking action. Preserve detection with an alert.
        DB::table('security_rules')
            ->whereIn('rule_name', ['bulk_inventory_change', 'inventory_discrepancy_spike'])
            ->where('action', 'block')
            ->update(['action' => 'alert']);
    }

    public function down(): void
    {
        // The former action cannot be identified safely after normalization.
    }
};
