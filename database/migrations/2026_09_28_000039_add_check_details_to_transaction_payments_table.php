<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transaction_payments', function (Blueprint $table) {
            $table->string('check_number', 100)->nullable()->after('payment_reference');
            $table->string('check_bank', 100)->nullable()->after('check_number');
            $table->date('check_date')->nullable()->after('check_bank');
        });
    }

    public function down(): void
    {
        Schema::table('transaction_payments', function (Blueprint $table) {
            $table->dropColumn(['check_number', 'check_bank', 'check_date']);
        });
    }
};
