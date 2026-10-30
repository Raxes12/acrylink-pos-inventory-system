<?php

namespace Tests\Feature;

use App\Http\Middleware\CheckBlockedEntity;
use App\Http\Middleware\DenyBlockedDevice;
use App\Http\Middleware\DetectInputAttack;
use App\Http\Middleware\DetectSessionAnomaly;
use App\Http\Middleware\TrackUserSession;
use App\Http\Middleware\ValidateAuthorizedDevice;
use App\Models\Brand;
use App\Models\InventoryStock;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductVariant;
use App\Models\StockAlert;
use App\Models\Supplier;
use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Models\Unit;
use App\Models\User;
use App\Services\InventoryMonitoringService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VariantInventoryProcessTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
        $this->withoutMiddleware([
            CheckBlockedEntity::class,
            DenyBlockedDevice::class,
            DetectInputAttack::class,
            DetectSessionAnomaly::class,
            TrackUserSession::class,
            ValidateAuthorizedDevice::class,
        ]);
    }

    public function test_inventory_staff_can_create_product_variant_with_initial_stock(): void
    {
        $this->actingAs($this->inventoryStaff());
        $supplier = Supplier::firstOrFail();

        $this->post(route('inventory.store'), $this->inventoryPayload([
            'name' => 'Variant Process Brake Pad',
            'code' => 'VP-BRAKE-001',
            'part_number' => 'BP-001',
            'brand' => 'Process Brand',
            'size' => 'Front',
            'color' => 'Black',
            'stock' => 11,
            'supplier_id' => $supplier->id,
        ]))->assertRedirect(route('inventory.index'))
            ->assertSessionHasNoErrors();

        $product = Product::where('name', 'Variant Process Brake Pad')->firstOrFail();
        $variant = ProductVariant::where('variant_code', 'VP-BRAKE-001')->firstOrFail();

        $this->assertSame($product->id, $variant->product_id);
        $this->assertSame('Process Brand', $variant->brand?->name);
        $this->assertSame('active', $variant->status);
        $this->assertDatabaseHas('inventory_stocks', [
            'variant_id' => $variant->id,
            'quantity_on_hand' => 11,
            'minimum_stock' => 4,
            'critical_stock' => 2,
        ]);
        $this->assertDatabaseHas('inventory_movements', [
            'variant_id' => $variant->id,
            'movement_type' => 'stock_in',
            'quantity_change' => 11,
            'quantity_before' => 0,
            'quantity_after' => 11,
            'reference_type' => 'variant_create',
        ]);
    }

    public function test_create_form_hides_status_and_new_variant_is_always_active(): void
    {
        $this->actingAs($this->inventoryStaff());

        $this->get(route('inventory.create'))
            ->assertOk()
            ->assertDontSee('name="status"', false);

        $payload = $this->inventoryPayload([
            'code' => 'VP-ACTIVE-DEFAULT',
            'name' => 'Active Default Product',
            'status' => 'inactive',
        ]);

        $this->post(route('inventory.store'), $payload)
            ->assertRedirect(route('inventory.index'))
            ->assertSessionHasNoErrors();

        $this->assertSame('active', ProductVariant::where('variant_code', 'VP-ACTIVE-DEFAULT')->value('status'));
    }

    public function test_add_variant_prefills_and_safely_reserves_the_next_prdv_code(): void
    {
        $this->actingAs($this->inventoryStaff());
        $this->makeVariant('PRDV-055');

        $this->get(route('inventory.create'))
            ->assertOk()
            ->assertSee('name="code" value="PRDV-056"', false)
            ->assertSee('name="suggested_code" value="PRDV-056"', false)
            ->assertSee('The next available code is filled automatically.');

        $automaticPayload = $this->inventoryPayload([
            'code' => 'PRDV-056',
            'suggested_code' => 'PRDV-056',
            'name' => 'Automatically Coded Variant',
        ]);

        $this->post(route('inventory.store'), $automaticPayload)
            ->assertRedirect(route('inventory.index'))
            ->assertSessionHasNoErrors();

        // A second user can submit a stale preview without creating a duplicate.
        $this->post(route('inventory.store'), array_merge($automaticPayload, [
            'name' => 'Concurrent Automatically Coded Variant',
        ]))->assertRedirect(route('inventory.index'))
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('product_variants', ['variant_code' => 'PRDV-056']);
        $this->assertDatabaseHas('product_variants', ['variant_code' => 'PRDV-057']);
        $this->assertDatabaseHas('document_sequences', [
            'sequence_key' => 'product-variants',
            'current_value' => 57,
        ]);
    }

    public function test_same_product_can_have_multiple_variant_records(): void
    {
        $this->actingAs($this->inventoryStaff());
        $supplier = Supplier::firstOrFail();

        $this->post(route('inventory.store'), $this->inventoryPayload([
            'name' => 'Variant Process Shock Absorber',
            'code' => 'VP-SHOCK-FRONT',
            'part_number' => 'SA-FRONT-001',
            'brand' => 'KYB',
            'size' => 'Front',
            'color' => 'Silver',
            'supplier_id' => $supplier->id,
        ]))->assertSessionHasNoErrors();

        $product = Product::where('name', 'Variant Process Shock Absorber')->firstOrFail();

        $this->post(route('inventory.store'), $this->inventoryPayload([
            'product_id' => $product->id,
            'code' => 'VP-SHOCK-REAR',
            'part_number' => 'SA-REAR-002',
            'brand' => 'Monroe',
            'size' => 'Rear',
            'color' => 'Black',
            'supplier_id' => $supplier->id,
        ]))->assertSessionHasNoErrors();

        $product->load('variants.brand');

        $this->assertCount(2, $product->variants);
        $this->assertTrue($product->variants->contains(fn ($variant) => $variant->part_number === 'SA-FRONT-001'
            && $variant->brand?->name === 'KYB'
            && $variant->size === 'Front'
            && $variant->color === 'Silver'));
        $this->assertTrue($product->variants->contains(fn ($variant) => $variant->part_number === 'SA-REAR-002'
            && $variant->brand?->name === 'Monroe'
            && $variant->size === 'Rear'
            && $variant->color === 'Black'));
    }

    public function test_pos_checkout_deducts_stock_only_from_selected_variant(): void
    {
        $this->actingAs($this->cashier());
        [$selectedVariant, $otherVariant] = $this->variantPair();
        $selectedBefore = (int) $selectedVariant->inventoryStock->quantity_on_hand;
        $otherBefore = (int) $otherVariant->inventoryStock->quantity_on_hand;

        $this->post(route('pos.checkout'), $this->checkoutPayload($selectedVariant, 3))
            ->assertSessionHasNoErrors();

        $this->assertSame($selectedBefore - 3, (int) $selectedVariant->fresh('inventoryStock')->inventoryStock->quantity_on_hand);
        $this->assertSame($otherBefore, (int) $otherVariant->fresh('inventoryStock')->inventoryStock->quantity_on_hand);
    }

    public function test_pos_checkout_cannot_sell_more_than_variant_stock(): void
    {
        $this->actingAs($this->cashier());
        $variant = $this->makeVariant('VP-LIMIT-001', ['quantity_on_hand' => 2]);

        $this->from(route('pos.index'))
            ->post(route('pos.checkout'), $this->checkoutPayload($variant, 3))
            ->assertRedirect(route('pos.index'))
            ->assertSessionHasErrors('items');

        $this->assertDatabaseCount('transactions', 0);
        $this->assertSame(2, (int) $variant->fresh('inventoryStock')->inventoryStock->quantity_on_hand);
    }

    public function test_transaction_item_stores_variant_id_and_snapshot_fields(): void
    {
        $this->actingAs($this->cashier());
        $variant = $this->makeVariant('VP-SNAP-001', [
            'quantity_on_hand' => 9,
        ], [
            'part_number' => 'SNAP-001',
            'size' => 'Compact',
            'color' => 'Red',
            'brand_name' => 'Snapshot Brand',
        ]);

        $this->post(route('pos.checkout'), $this->checkoutPayload($variant, 2))
            ->assertSessionHasNoErrors();

        $transaction = Transaction::latest('id')->firstOrFail();

        $this->assertDatabaseHas('transaction_items', [
            'transaction_id' => $transaction->id,
            'variant_id' => $variant->id,
            'product_name_snapshot' => $variant->product->name,
            'part_number_snapshot' => 'SNAP-001',
            'brand_snapshot' => 'Snapshot Brand',
            'size_snapshot' => 'Compact',
            'color_snapshot' => 'Red',
            'unit_snapshot' => 'PCS',
            'quantity' => 2,
            'subtotal' => number_format((float) $variant->selling_price * 2, 2, '.', ''),
        ]);
    }

    public function test_refund_with_restock_increases_correct_variant_stock(): void
    {
        $this->actingAs($this->cashier());
        [$selectedVariant, $otherVariant] = $this->variantPair();
        $selectedStartingStock = (int) $selectedVariant->inventoryStock->quantity_on_hand;
        $otherStartingStock = (int) $otherVariant->inventoryStock->quantity_on_hand;

        $this->post(route('pos.checkout'), $this->checkoutPayload($selectedVariant, 2))
            ->assertSessionHasNoErrors();

        $transaction = Transaction::latest('id')->firstOrFail();
        $transactionItem = TransactionItem::where('transaction_id', $transaction->id)->firstOrFail();
        $stockAfterSale = (int) $selectedVariant->fresh('inventoryStock')->inventoryStock->quantity_on_hand;

        $this->actingAs($this->admin());
        $this->post(route('pos.refund.process', $transaction), [
            'reason' => 'Variant restock test',
            'refund_method' => 'cash',
            'notes' => null,
            'items' => [[
                'transaction_item_id' => $transactionItem->id,
                'quantity' => 1,
                'restock' => 1,
            ]],
        ])->assertRedirect(route('pos.receipt', $transaction))
            ->assertSessionHasNoErrors();

        $this->assertSame($stockAfterSale + 1, (int) $selectedVariant->fresh('inventoryStock')->inventoryStock->quantity_on_hand);
        $this->assertSame($selectedStartingStock - 1, (int) $selectedVariant->fresh('inventoryStock')->inventoryStock->quantity_on_hand);
        $this->assertSame($otherStartingStock, (int) $otherVariant->fresh('inventoryStock')->inventoryStock->quantity_on_hand);
        $this->assertDatabaseHas('inventory_movements', [
            'variant_id' => $selectedVariant->id,
            'movement_type' => 'sale_return',
            'quantity_change' => 1,
            'reference_type' => 'refund',
        ]);
    }

    public function test_stock_alert_opens_when_variant_quantity_reaches_minimum_stock(): void
    {
        $this->actingAs($this->inventoryStaff());
        $variant = $this->makeVariant('VP-LOW-001', [
            'quantity_on_hand' => 8,
            'minimum_stock' => 5,
            'critical_stock' => 1,
        ]);

        $this->post(route('inventory.adjust-stock', $variant), [
            'type' => 'stock_out',
            'quantity' => 3,
            'remarks' => 'Move to low stock threshold.',
        ])->assertSessionHasNoErrors();

        $this->assertDatabaseHas('stock_alerts', [
            'variant_id' => $variant->id,
            'alert_type' => 'low_stock',
            'alert_status' => 'open',
        ]);
    }

    public function test_stock_alert_resolves_when_stock_is_replenished(): void
    {
        $this->actingAs($this->inventoryStaff());
        $variant = $this->makeVariant('VP-RESOLVE-001', [
            'quantity_on_hand' => 5,
            'minimum_stock' => 5,
            'critical_stock' => 1,
        ]);

        app(InventoryMonitoringService::class)->syncStockAlert($variant);

        $this->assertDatabaseHas('stock_alerts', [
            'variant_id' => $variant->id,
            'alert_status' => 'open',
        ]);

        $this->post(route('inventory.adjust-stock', $variant), [
            'type' => 'stock_in',
            'quantity' => 2,
            'remarks' => 'Replenish above minimum.',
        ])->assertSessionHasNoErrors();

        $this->assertSame(0, StockAlert::where('variant_id', $variant->id)
            ->where('alert_status', 'open')
            ->count());
        $this->assertDatabaseHas('stock_alerts', [
            'variant_id' => $variant->id,
            'alert_status' => 'resolved',
        ]);
    }

    public function test_inventory_movement_records_quantity_before_and_after(): void
    {
        $this->actingAs($this->inventoryStaff());
        $variant = $this->makeVariant('VP-MOVE-001', [
            'quantity_on_hand' => 6,
        ]);

        $this->post(route('inventory.adjust-stock', $variant), [
            'type' => 'stock_in',
            'quantity' => 4,
            'remarks' => 'Before after movement test.',
        ])->assertSessionHasNoErrors();

        $this->assertDatabaseHas('inventory_movements', [
            'variant_id' => $variant->id,
            'movement_type' => 'stock_in',
            'quantity_change' => 4,
            'quantity_before' => 6,
            'quantity_after' => 10,
            'reference_type' => 'manual_adjustment',
        ]);
    }

    public function test_cashier_cannot_access_inventory_editing_routes(): void
    {
        $cashier = $this->cashier();
        $variant = ProductVariant::firstOrFail();

        $this->actingAs($cashier);

        $this->get(route('inventory.index'))->assertForbidden();
        $this->get(route('inventory.stock-overview'))->assertForbidden();
        $this->get(route('inventory.create'))->assertForbidden();
        $this->post(route('inventory.store'), $this->inventoryPayload([
            'code' => 'CASHIER-BLOCKED-001',
        ]))->assertForbidden();
        $this->get(route('inventory.edit', $variant))->assertForbidden();
        $this->put(route('inventory.update', $variant), $this->inventoryPayload([
            'product_id' => $variant->product_id,
            'code' => $variant->variant_code,
        ]))->assertForbidden();
        $this->post(route('inventory.adjust-stock', $variant), [
            'type' => 'stock_in',
            'quantity' => 1,
        ])->assertForbidden();
        $this->delete(route('inventory.destroy', $variant))->assertForbidden();

        $this->assertDatabaseMissing('product_variants', [
            'variant_code' => 'CASHIER-BLOCKED-001',
        ]);
    }

    private function inventoryStaff(): User
    {
        return User::where('role', User::ROLE_INVENTORY)->firstOrFail();
    }

    private function admin(): User
    {
        return User::where('role', User::ROLE_ADMIN)->firstOrFail();
    }

    private function cashier(): User
    {
        return User::where('role', User::ROLE_CASHIER)->firstOrFail();
    }

    private function inventoryPayload(array $overrides = []): array
    {
        return array_merge([
            'product_id' => '',
            'code' => 'VP-DEFAULT-001',
            'barcode' => '',
            'part_number' => 'VP-PN-001',
            'name' => 'Variant Process Product',
            'description' => 'Created by feature test.',
            'brand' => 'Variant Test Brand',
            'size' => 'Standard',
            'color' => 'Black',
            'category' => ProductCategory::firstOrFail()->name,
            'price' => 1200,
            'cost' => 750,
            'stock' => 10,
            'min_stock' => 4,
            'critical_stock' => 2,
            'unit' => Unit::firstOrFail()->code,
            'supplier_id' => Supplier::firstOrFail()->id,
            'status' => 'active',
        ], $overrides);
    }

    private function variantPair(): array
    {
        $product = Product::create([
            'name' => 'Variant Pair Test Product',
            'category' => ProductCategory::firstOrFail()->name,
            'description' => 'Two variants for checkout tests.',
            'status' => 'active',
        ]);

        return [
            $this->makeVariant('VP-PAIR-A', ['quantity_on_hand' => 10], [
                'product_id' => $product->id,
                'part_number' => 'PAIR-A',
                'brand_name' => 'Pair Brand A',
                'size' => 'Front',
                'color' => 'Black',
            ]),
            $this->makeVariant('VP-PAIR-B', ['quantity_on_hand' => 12], [
                'product_id' => $product->id,
                'part_number' => 'PAIR-B',
                'brand_name' => 'Pair Brand B',
                'size' => 'Rear',
                'color' => 'Silver',
            ]),
        ];
    }

    private function makeVariant(string $code, array $stockOverrides = [], array $variantOverrides = []): ProductVariant
    {
        $product = isset($variantOverrides['product_id'])
            ? Product::findOrFail($variantOverrides['product_id'])
            : Product::create([
                'name' => $variantOverrides['product_name'] ?? "Product {$code}",
                'category' => ProductCategory::firstOrFail()->name,
                'description' => 'Variant process test product.',
                'status' => 'active',
            ]);
        $brandName = $variantOverrides['brand_name'] ?? 'Variant Process Brand';
        $brand = Brand::firstOrCreate(['name' => $brandName]);

        $variant = ProductVariant::create([
            'product_id' => $product->id,
            'brand_id' => $brand->id,
            'unit_id' => Unit::where('code', 'PCS')->value('id') ?: Unit::firstOrFail()->id,
            'supplier_id' => Supplier::firstOrFail()->id,
            'variant_code' => $code,
            'barcode' => $variantOverrides['barcode'] ?? null,
            'part_number' => $variantOverrides['part_number'] ?? "{$code}-PN",
            'size' => $variantOverrides['size'] ?? 'Standard',
            'color' => $variantOverrides['color'] ?? 'Black',
            'selling_price' => $variantOverrides['selling_price'] ?? 500,
            'cost_price' => $variantOverrides['cost_price'] ?? 300,
            'status' => 'active',
        ]);

        InventoryStock::create(array_merge([
            'variant_id' => $variant->id,
            'quantity_on_hand' => 10,
            'minimum_stock' => 3,
            'critical_stock' => 1,
        ], $stockOverrides));

        return $variant->load('product', 'brand', 'unit', 'inventoryStock');
    }

    private function checkoutPayload(ProductVariant $variant, int $quantity): array
    {
        $payment = (float) $variant->selling_price * $quantity;

        return [
            'items' => [[
                'variant_id' => $variant->id,
                'quantity' => $quantity,
                'unit_price' => $variant->selling_price,
            ]],
            'discount' => 0,
            'payment_method' => 'cash',
            'payment_reference' => null,
            'payment' => $payment,
        ];
    }
}
