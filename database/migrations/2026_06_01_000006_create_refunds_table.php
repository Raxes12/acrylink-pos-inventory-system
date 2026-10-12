<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        $this->createRefundsTable();
        $this->createRefundItemsTable();
    }

    public function down(): void
    {
        Schema::dropIfExists('refund_items');
        Schema::dropIfExists('refunds');
    }

    private function createRefundsTable(): void
    {
        Schema::create('refunds', function (Blueprint $table) {
            $table->id();
            $table->string('refund_no', 30)->unique();
            $table->foreignId('transaction_id')->constrained()->cascadeOnDelete();
            $table->foreignId('processed_by')->constrained('users');
            $table->string('reason', 500)->nullable();
            $table->enum('refund_type', ['full', 'partial'])->default('full');
            $table->decimal('refund_amount', 10, 2);
            $table->foreignId('refund_method_id')->nullable()->constrained('payment_methods')->nullOnDelete();
            $table->string('notes', 1000)->nullable();
            $table->timestamps();

            $table->index('transaction_id');
        });
    }

    private function createRefundItemsTable(): void
    {
        Schema::create('refund_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('refund_id')->constrained()->cascadeOnDelete();
            $table->foreignId('transaction_item_id')->constrained()->cascadeOnDelete();
            $table->string('product_name', 200);
            $table->integer('quantity');
            $table->decimal('unit_price', 10, 2);
            $table->boolean('restock')->default(true);
        });
    }
};
