<?php

namespace Tests\Feature;

use App\Models\Brand;
use App\Models\InventoryMovement;
use App\Models\InventoryStock;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\StockAlert;
use App\Models\StockReceipt;
use App\Models\StockReceiptItem;
use App\Models\Supplier;
use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Models\Unit;
use App\Models\User;
use App\Services\InventoryMonitoringService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class VariantInventorySchemaTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
    }

    public function test_variant_inventory_tables_and_ledger_links_exist(): void
    {
        foreach (['brands', 'product_variants', 'inventory_stocks', 'stock_receipts', 'stock_receipt_items'] as $table) {
            $this->assertTrue(Schema::hasTable($table), "Expected {$table} table to exist.");
        }

        foreach (['transaction_items', 'inventory_movements', 'stock_alerts'] as $table) {
            $this->assertTrue(Schema::hasColumn($table, 'variant_id'), "Expected {$table}.variant_id to exist.");
        }

        $this->assertFalse(Schema::hasColumn('product_variants', 'model'), 'Expected product_variants.model to be removed.');

        foreach (['quantity_change', 'quantity_before', 'quantity_after'] as $column) {
            $this->assertTrue(Schema::hasColumn('inventory_movements', $column), "Expected inventory_movements.{$column} to exist.");
        }

        foreach ([
            'product_name_snapshot',
            'part_number_snapshot',
            'brand_snapshot',
            'size_snapshot',
            'color_snapshot',
            'unit_snapshot',
            'subtotal',
        ] as $column) {
            $this->assertTrue(Schema::hasColumn('transaction_items', $column), "Expected transaction_items.{$column} to exist.");
        }

        foreach (['product_id', 'quantity'] as $column) {
            $this->assertFalse(Schema::hasColumn('inventory_movements', $column), "Expected inventory_movements.{$column} to be removed.");
        }

        $this->assertFalse(Schema::hasColumn('stock_alerts', 'product_id'), 'Expected stock_alerts.product_id to be removed.');
    }

    public function test_products_table_keeps_only_general_product_identity(): void
    {
        foreach (['category_id', 'name', 'description', 'status'] as $column) {
            $this->assertTrue(Schema::hasColumn('products', $column), "Expected products.{$column} to exist.");
        }

        foreach (['code', 'barcode', 'brand', 'variant', 'size', 'color', 'price', 'cost', 'stock', 'min_stock', 'unit_id', 'supplier_id'] as $column) {
            $this->assertFalse(Schema::hasColumn('products', $column), "Expected products.{$column} to be moved out.");
        }
    }

    public function test_seeded_product_details_are_moved_to_variant_and_stock_tables(): void
    {
        $variant = ProductVariant::where('variant_code', 'PRDV-0001')
            ->with('product', 'brand', 'supplier', 'unit', 'inventoryStock')
            ->firstOrFail();

        $this->assertSame('Adapter Fitting', $variant->product->name);
        $this->assertNull($variant->part_number);
        $this->assertNull($variant->brand);
        $this->assertSame('pcs', $variant->unit?->code);
        $this->assertSame("3G' HYDRAULIC POWER CORPORATION", $variant->supplier?->name);
        $this->assertSame('39.00', $variant->selling_price);
        $this->assertSame('26.00', $variant->cost_price);
        $this->assertSame(30, $variant->inventoryStock->quantity_on_hand);
        $this->assertSame(10, $variant->inventoryStock->minimum_stock);

        $product = $variant->product->fresh();
        $this->assertSame('PRDV-0001', $product->code);
        $this->assertSame(39.0, $product->price);
        $this->assertSame(30, $product->stock);
    }

    public function test_variant_inventory_models_have_expected_relationships(): void
    {
        $product = Product::firstOrFail();
        $supplier = Supplier::firstOrFail();
        $unit = Unit::firstOrFail();
        $user = User::where('role', User::ROLE_ADMIN)->firstOrFail();
        $brand = Brand::create(['name' => 'Test Brand']);

        $variant = ProductVariant::create([
            'product_id' => $product->id,
            'brand_id' => $brand->id,
            'unit_id' => $unit->id,
            'supplier_id' => $supplier->id,
            'variant_code' => 'VAR-TEST-001',
            'barcode' => '899999000001',
            'part_number' => 'PN-001',
            'size' => 'Standard',
            'color' => 'Black',
            'selling_price' => 1250,
            'cost_price' => 800,
            'status' => 'active',
        ]);

        $stock = InventoryStock::create([
            'variant_id' => $variant->id,
            'quantity_on_hand' => 12,
            'minimum_stock' => 3,
            'critical_stock' => 1,
            'last_received_at' => now()->toDateString(),
        ]);

        $receipt = StockReceipt::create([
            'supplier_id' => $supplier->id,
            'received_by' => $user->id,
            'receipt_no' => 'SR-TEST-001',
            'date_received' => now()->toDateString(),
            'remarks' => 'Initial variant receipt test.',
        ]);

        $receiptItem = StockReceiptItem::create([
            'receipt_id' => $receipt->id,
            'variant_id' => $variant->id,
            'quantity_received' => 12,
            'unit_cost' => 800,
            'total_cost' => 9600,
        ]);

        $transaction = Transaction::create([
            'receipt_no' => 'RCP-VAR-TEST',
            'date' => now()->toDateString(),
            'time' => now()->toTimeString(),
            'cashier_id' => $user->id,
            'subtotal' => 1250,
            'discount' => 0,
            'total' => 1250,
            'payment_method' => 'cash',
            'payment' => 1250,
            'change_due' => 0,
            'status' => 'completed',
        ]);

        TransactionItem::create([
            'transaction_id' => $transaction->id,
            'product_id' => $product->id,
            'variant_id' => $variant->id,
            'product_name' => $product->name,
            'quantity' => 1,
            'unit_price' => 1250,
        ]);

        InventoryMovement::create([
            'variant_id' => $variant->id,
            'user_id' => $user->id,
            'movement_type' => 'stock_in',
            'quantity_change' => 12,
            'quantity_before' => 0,
            'quantity_after' => 12,
            'reference_type' => 'stock_receipt',
            'reference_id' => $receipt->id,
            'remarks' => 'Variant receipt test movement.',
        ]);

        StockAlert::create([
            'variant_id' => $variant->id,
            'alert_type' => 'low_stock',
            'message' => 'Variant stock alert test.',
            'alert_status' => 'open',
        ]);

        $this->assertTrue($product->variants->contains($variant));
        $this->assertTrue($brand->productVariants->contains($variant));
        $this->assertTrue($supplier->productVariants->contains($variant));
        $this->assertTrue($unit->productVariants->contains($variant));

        $this->assertTrue($variant->product->is($product));
        $this->assertTrue($variant->brand->is($brand));
        $this->assertTrue($variant->unit->is($unit));
        $this->assertTrue($variant->supplier->is($supplier));
        $this->assertTrue($variant->inventoryStock->is($stock));
        $this->assertSame(1, $variant->transactionItems()->count());
        $this->assertSame(1, $variant->inventoryMovements()->count());
        $this->assertSame(1, $variant->stockAlerts()->count());

        $this->assertTrue($stock->productVariant->is($variant));
        $this->assertTrue($receipt->supplier->is($supplier));
        $this->assertTrue($receipt->receivedBy->is($user));
        $this->assertTrue($receipt->items->contains($receiptItem));
        $this->assertTrue($receiptItem->stockReceipt->is($receipt));
        $this->assertTrue($receiptItem->productVariant->is($variant));
    }

    public function test_stock_alerts_are_created_per_variant_with_critical_level(): void
    {
        $product = Product::firstOrFail();
        $unit = Unit::firstOrFail();

        $criticalVariant = ProductVariant::create([
            'product_id' => $product->id,
            'unit_id' => $unit->id,
            'variant_code' => 'VAR-CRITICAL-001',
            'selling_price' => 900,
            'cost_price' => 500,
            'status' => 'active',
        ]);

        InventoryStock::create([
            'variant_id' => $criticalVariant->id,
            'quantity_on_hand' => 1,
            'minimum_stock' => 5,
            'critical_stock' => 2,
        ]);

        $normalVariant = ProductVariant::create([
            'product_id' => $product->id,
            'unit_id' => $unit->id,
            'variant_code' => 'VAR-NORMAL-001',
            'selling_price' => 950,
            'cost_price' => 520,
            'status' => 'active',
        ]);

        InventoryStock::create([
            'variant_id' => $normalVariant->id,
            'quantity_on_hand' => 20,
            'minimum_stock' => 5,
            'critical_stock' => 2,
        ]);

        app(InventoryMonitoringService::class)->syncStockAlert($criticalVariant);
        app(InventoryMonitoringService::class)->syncStockAlert($normalVariant);

        $this->assertDatabaseHas('stock_alerts', [
            'variant_id' => $criticalVariant->id,
            'alert_type' => 'critical_stock',
            'alert_status' => 'open',
        ]);

        $this->assertDatabaseMissing('stock_alerts', [
            'variant_id' => $normalVariant->id,
            'alert_status' => 'open',
        ]);
    }

    public function test_inventory_monitoring_service_records_variant_movement_by_variant_id(): void
    {
        $variant = ProductVariant::where('variant_code', 'PRDV-0001')->with('inventoryStock')->firstOrFail();
        $startingQuantity = (int) $variant->inventoryStock->quantity_on_hand;

        $movement = app(InventoryMonitoringService::class)->recordMovement(
            $variant->id,
            'stock_out',
            -3,
            'service_test',
            $variant->id,
            'Service variant movement test.'
        );

        $this->assertSame($variant->id, $movement->variant_id);
        $this->assertSame(-3, $movement->quantity_change);
        $this->assertSame($startingQuantity, $movement->quantity_before);
        $this->assertSame($startingQuantity - 3, $movement->quantity_after);

        $this->assertDatabaseHas('inventory_stocks', [
            'variant_id' => $variant->id,
            'quantity_on_hand' => $startingQuantity - 3,
        ]);

        $this->assertDatabaseHas('inventory_movements', [
            'variant_id' => $variant->id,
            'movement_type' => 'stock_out',
            'quantity_change' => -3,
            'quantity_before' => $startingQuantity,
            'quantity_after' => $startingQuantity - 3,
            'reference_type' => 'service_test',
            'reference_id' => $variant->id,
        ]);
    }
}
