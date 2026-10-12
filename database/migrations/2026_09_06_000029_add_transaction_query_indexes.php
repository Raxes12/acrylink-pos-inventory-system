<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->index(['date', 'time'], 'transactions_date_time_index');
            $table->index(['cashier_id', 'date', 'time'], 'transactions_cashier_date_time_index');
        });
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropIndex('transactions_cashier_date_time_index');
            $table->dropIndex('transactions_date_time_index');
        });
    }
};
