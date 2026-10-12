<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('refunds', 'receivable_adjustment_amount')) {
            Schema::table('refunds', function (Blueprint $table) {
                $table->decimal('receivable_adjustment_amount', 12, 2)->default(0)->after('refund_amount');
            });
        }
        if (! Schema::hasColumn('refunds', 'reimbursement_amount')) {
            Schema::table('refunds', function (Blueprint $table) {
                $table->decimal('reimbursement_amount', 12, 2)->default(0)->after('receivable_adjustment_amount');
            });
        }

        // Historical refunds used the refund method as an immediate payout.
        // Preserve that financial meaning rather than reclassifying old money movement.
        DB::table('refunds')->update([
            'receivable_adjustment_amount' => 0,
            'reimbursement_amount' => DB::raw('refund_amount'),
        ]);
    }

    public function down(): void
    {
        if (Schema::hasColumn('refunds', 'reimbursement_amount')) {
            Schema::table('refunds', fn (Blueprint $table) => $table->dropColumn('reimbursement_amount'));
        }
        if (Schema::hasColumn('refunds', 'receivable_adjustment_amount')) {
            Schema::table('refunds', fn (Blueprint $table) => $table->dropColumn('receivable_adjustment_amount'));
        }
    }
};
