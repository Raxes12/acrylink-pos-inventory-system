<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        $this->createTransactionsTable();
        $this->createTransactionItemsTable();
    }

    public function down(): void
    {
        Schema::dropIfExists('transaction_items');
        Schema::dropIfExists('transactions');
    }

    private function createTransactionsTable(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->string('receipt_no', 20)->unique();
            $table->date('date');
            $table->time('time');
            $table->foreignId('cashier_id')->constrained('users');
            $table->decimal('subtotal', 10, 2);
            $table->decimal('discount', 10, 2)->default(0);
            $table->decimal('total', 10, 2);
            $table->foreignId('payment_method_id')->nullable()->constrained('payment_methods')->nullOnDelete();
            $table->string('payment_reference', 100)->nullable();
            $table->string('payment_card_type', 50)->nullable();
            $table->string('payment_card_last4', 4)->nullable();
            $table->string('payment_payer_name', 120)->nullable();
            $table->enum('payment_status', ['pending', 'confirmed'])->default('confirmed');
            $table->string('check_number', 100)->nullable();
            $table->string('check_bank', 100)->nullable();
            $table->date('check_date')->nullable();
            $table->decimal('payment', 10, 2);
            $table->decimal('change_due', 10, 2);
            $table->enum('status', ['completed', 'refunded', 'partial_refund', 'voided'])->default('completed');
            $table->timestamps();

            $table->index('date');
            $table->index('cashier_id');
            $table->index('status');
        });
    }

    private function createTransactionItemsTable(): void
    {
        Schema::create('transaction_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transaction_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            $table->string('product_name', 200);
            $table->integer('quantity');
            $table->decimal('unit_price', 10, 2);
        });
    }
};
