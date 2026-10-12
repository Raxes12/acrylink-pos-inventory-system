<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        $this->createBrandsTable();
        $this->createProductVariantsTable();
        $this->createInventoryStocksTable();
        $this->createStockReceiptsTable();
        $this->createStockReceiptItemsTable();
        $this->createInventoryMovementsTable();
        $this->createStockAlertsTable();
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_alerts');
        Schema::dropIfExists('inventory_movements');
        Schema::dropIfExists('stock_receipt_items');
        Schema::dropIfExists('stock_receipts');
        Schema::dropIfExists('inventory_stocks');
        Schema::dropIfExists('product_variants');
        Schema::dropIfExists('brands');
    }

    private function createBrandsTable(): void
    {
        Schema::create('brands', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->timestamps();
        });
    }

    private function createProductVariantsTable(): void
    {
        Schema::create('product_variants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('brand_id')->nullable()->constrained('brands')->nullOnDelete();
            $table->foreignId('unit_id')->nullable()->constrained('units')->nullOnDelete();
            $table->foreignId('supplier_id')->nullable()->constrained('suppliers')->nullOnDelete();
            $table->string('variant_code')->unique();
            $table->string('barcode')->nullable();
            $table->string('part_number')->nullable();
            $table->string('size')->nullable();
            $table->string('color')->nullable();
            $table->string('image_path')->nullable();
            $table->decimal('selling_price', 10, 2);
            $table->decimal('cost_price', 10, 2);
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();

            $table->index('barcode');
            $table->index('status');
            $table->index(['product_id', 'status']);
        });
    }

    private function createInventoryStocksTable(): void
    {
        Schema::create('inventory_stocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('variant_id')->unique()->constrained('product_variants')->cascadeOnDelete();
            $table->integer('quantity_on_hand')->default(0);
            $table->integer('minimum_stock')->default(0);
            $table->integer('critical_stock')->default(0);
            $table->date('last_received_at')->nullable();
            $table->timestamps();
        });
    }

    private function createStockReceiptsTable(): void
    {
        Schema::create('stock_receipts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('supplier_id')->nullable()->constrained('suppliers')->nullOnDelete();
            $table->foreignId('received_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('receipt_no')->unique();
            $table->date('date_received');
            $table->text('remarks')->nullable();
            $table->timestamps();

            $table->index('date_received');
        });
    }

    private function createStockReceiptItemsTable(): void
    {
        Schema::create('stock_receipt_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('receipt_id')->constrained('stock_receipts')->cascadeOnDelete();
            $table->foreignId('variant_id')->constrained('product_variants')->cascadeOnDelete();
            $table->integer('quantity_received');
            $table->decimal('unit_cost', 10, 2);
            $table->decimal('total_cost', 10, 2);
            $table->timestamps();

            $table->index(['receipt_id', 'variant_id']);
        });
    }

    private function createInventoryMovementsTable(): void
    {
        Schema::create('inventory_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('variant_id')->constrained('product_variants')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->enum('movement_type', ['stock_in', 'stock_out', 'sale', 'adjustment', 'sale_return']);
            $table->integer('quantity_change');
            $table->integer('quantity_before')->default(0);
            $table->integer('quantity_after')->default(0);
            $table->string('reference_type', 50)->nullable();
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->text('remarks')->nullable();
            $table->dateTime('created_at')->useCurrent();

            $table->index(['variant_id', 'created_at']);
            $table->index(['movement_type', 'created_at']);
        });
    }

    private function createStockAlertsTable(): void
    {
        Schema::create('stock_alerts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('variant_id')->constrained('product_variants')->cascadeOnDelete();
            $table->enum('alert_type', ['low_stock', 'critical_stock', 'out_of_stock']);
            $table->string('message', 255);
            $table->enum('alert_status', ['open', 'resolved'])->default('open');
            $table->dateTime('created_at')->useCurrent();
            $table->dateTime('resolved_at')->nullable();

            $table->index(['variant_id', 'alert_status']);
            $table->index(['alert_status', 'created_at']);
        });
    }
};
