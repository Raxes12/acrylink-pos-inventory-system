<?php

namespace Tests\Feature;

use App\Http\Middleware\CheckBlockedEntity;
use App\Http\Middleware\DenyBlockedDevice;
use App\Http\Middleware\DetectInputAttack;
use App\Http\Middleware\DetectSessionAnomaly;
use App\Http\Middleware\TrackUserSession;
use App\Http\Middleware\ValidateAuthorizedDevice;
use App\Models\InventoryStock;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Refund;
use App\Models\RefundItem;
use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class POSRefundValidationTest extends TestCase
{
    use RefreshDatabase;

    private Product $product;

    private ProductVariant $variant;

    private Transaction $transaction;

    private TransactionItem $transactionItem;

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

        $admin = User::where('role', User::ROLE_ADMIN)->firstOrFail();
        $this->actingAs($admin);
        $this->variant = ProductVariant::with('product', 'brand', 'unit', 'inventoryStock')->firstOrFail();
        $this->product = $this->variant->product;
        $this->transaction = Transaction::create([
            'receipt_no' => 'RCP-REFUND-TEST',
            'date' => now()->toDateString(),
            'time' => now()->toTimeString(),
            'cashier_id' => $admin->id,
            'subtotal' => 500,
            'discount' => 50,
            'total' => 450,
            'payment_method' => 'cash',
            'payment' => 500,
            'change_due' => 50,
            'status' => 'completed',
        ]);
        $this->transactionItem = TransactionItem::create([
            'transaction_id' => $this->transaction->id,
            'product_id' => $this->product->id,
            'variant_id' => $this->variant->id,
            'product_name' => $this->product->name,
            'product_name_snapshot' => $this->product->name,
            'part_number_snapshot' => $this->variant->part_number,
            'brand_snapshot' => $this->variant->brand?->name,
            'size_snapshot' => $this->variant->size,
            'color_snapshot' => $this->variant->color,
            'unit_snapshot' => $this->variant->unit?->code ?? 'pcs',
            'quantity' => 5,
            'unit_price' => 100,
            'subtotal' => 500,
        ]);
    }

    public function test_partial_and_full_refunds_respect_remaining_quantity_and_restock_choice(): void
    {
        $startingStock = (int) $this->variant->inventoryStock->quantity_on_hand;
        $otherVariant = ProductVariant::create([
            'product_id' => $this->product->id,
            'unit_id' => $this->variant->unit_id,
            'variant_code' => 'REFUND-OTHER-001',
            'selling_price' => 125,
            'cost_price' => 80,
            'status' => 'active',
        ]);
        InventoryStock::create([
            'variant_id' => $otherVariant->id,
            'quantity_on_hand' => 7,
            'minimum_stock' => 2,
            'critical_stock' => 1,
        ]);

        $this->post(route('pos.refund.process', $this->transaction), $this->refundPayload(2, 1))
            ->assertRedirect(route('pos.receipt', $this->transaction))
            ->assertSessionHasNoErrors();

        $firstRefund = Refund::firstOrFail();
        $firstRefundItem = RefundItem::firstOrFail();

        $this->assertSame('partial', $firstRefund->refund_type);
        $this->assertSame('180.00', $firstRefund->refund_amount);
        $this->assertTrue($firstRefundItem->restock);
        $this->assertDatabaseHas('inventory_stocks', [
            'variant_id' => $this->variant->id,
            'quantity_on_hand' => $startingStock + 2,
        ]);
        $this->assertDatabaseHas('inventory_stocks', [
            'variant_id' => $otherVariant->id,
            'quantity_on_hand' => 7,
        ]);
        $this->assertDatabaseHas('inventory_movements', [
            'variant_id' => $this->variant->id,
            'movement_type' => 'sale_return',
            'quantity_change' => 2,
            'quantity_before' => $startingStock,
            'quantity_after' => $startingStock + 2,
            'reference_type' => 'refund',
            'reference_id' => $firstRefund->id,
        ]);
        $this->assertSame('partial_refund', $this->transaction->fresh()->status);

        $stockAfterPartialRefund = (int) $this->variant->fresh('inventoryStock')->inventoryStock->quantity_on_hand;

        $this->from(route('pos.refund.form', $this->transaction))
            ->post(route('pos.refund.process', $this->transaction), $this->refundPayload(4, 1))
            ->assertRedirect(route('pos.refund.form', $this->transaction))
            ->assertSessionHasErrors([
                'items' => "Cannot refund 4 of '{$this->transactionItem->product_name}'. Only 3 item(s) remain refundable after previous refunds.",
            ]);

        $this->assertDatabaseCount('refunds', 1);
        $this->assertSame($stockAfterPartialRefund, (int) $this->variant->fresh('inventoryStock')->inventoryStock->quantity_on_hand);

        $this->post(route('pos.refund.process', $this->transaction), $this->refundPayload(3))
            ->assertRedirect(route('pos.receipt', $this->transaction))
            ->assertSessionHasNoErrors();

        $finalRefund = Refund::latest('id')->firstOrFail();
        $finalRefundItem = RefundItem::latest('id')->firstOrFail();

        $this->assertSame('full', $finalRefund->refund_type);
        $this->assertSame('270.00', $finalRefund->refund_amount);
        $this->assertFalse($finalRefundItem->restock);
        $this->assertSame($stockAfterPartialRefund, (int) $this->variant->fresh('inventoryStock')->inventoryStock->quantity_on_hand);
        $fullyRefundedTransaction = $this->transaction->fresh()->load('refunds.items');
        $this->assertSame('refunded', $fullyRefundedTransaction->status);
        $this->assertSame(0.0, $fullyRefundedTransaction->netSubtotal());
        $this->assertSame(0.0, $fullyRefundedTransaction->netDiscount());
        $this->assertSame(0.0, $fullyRefundedTransaction->netTotal());
    }

    public function test_refund_form_renders_with_its_javascript_configuration(): void
    {
        $this->get(route('pos.refund.form', $this->transaction))
            ->assertOk()
            ->assertSee('Return / Refund')
            ->assertSee('<script type="application/json" id="refundConfig">', false)
            ->assertSee('"subtotal":"500.00"', false)
            ->assertSee('"discount":"50.00"', false)
            ->assertSee('"remaining":450', false)
            ->assertSee($this->transactionItem->product_name_snapshot)
            ->assertSee($this->transactionItem->unit_snapshot ? 'Unit: '.$this->transactionItem->unit_snapshot : 'pcs');
    }

    public function test_sales_report_uses_net_values_after_a_partial_refund(): void
    {
        $this->post(route('pos.refund.process', $this->transaction), $this->refundPayload(2, 1))
            ->assertRedirect(route('pos.receipt', $this->transaction));

        $this->get(route('reports.index'))
            ->assertOk()
            ->assertViewHas('totalRefunds', fn ($value) => (float) $value === 180.0)
            ->assertViewHas('totalSales', fn ($value) => (float) $value === 270.0)
            ->assertViewHas('grossRevenue', fn ($value) => (float) $value === 300.0)
            ->assertViewHas('totalDiscount', fn ($value) => (float) $value === 30.0)
            ->assertViewHas('avgSale', fn ($value) => (float) $value === 270.0)
            ->assertSee('After discounts and &#8369;180.00 in refunds', false)
            ->assertSee('&minus; &#8369;180.00', false)
            ->assertSee('&#8369;270.00', false);
    }

    public function test_sales_report_export_includes_refund_and_net_total(): void
    {
        $this->post(route('pos.refund.process', $this->transaction), $this->refundPayload(2, 1))
            ->assertRedirect(route('pos.receipt', $this->transaction));

        $response = $this->get(route('reports.export'));

        $response->assertOk();
        $csv = $response->streamedContent();

        $this->assertStringContainsString('"Original Total",Refunded,"Net Total"', $csv);
        $this->assertStringContainsString('450.00,180.00,270.00', $csv);
    }

    public function test_dashboard_and_header_use_net_sales_after_refund(): void
    {
        $this->post(route('pos.refund.process', $this->transaction), $this->refundPayload(2, 1))
            ->assertRedirect(route('pos.receipt', $this->transaction));

        $this->get(route('dashboard'))
            ->assertOk()
            ->assertViewHas('todaySales', fn ($value) => (float) $value === 270.0)
            ->assertViewHas('salesChart', function ($chart) {
                return (float) collect($chart)->last()['total'] === 270.0;
            })
            ->assertViewHas('topProducts', function ($products) {
                return $products->contains(function ($product) {
                    return str_starts_with($product->product_name, $this->transactionItem->product_name_snapshot)
                        && (int) $product->units_sold === 3;
                });
            })
            ->assertDontSee('class="topbar-pill pill-sales"', false)
            ->assertSee('data-count="270"', false);
    }

    public function test_restock_value_must_be_boolean(): void
    {
        $payload = $this->refundPayload(1);
        $payload['items'][0]['restock'] = 'invalid';

        $this->post(route('pos.refund.process', $this->transaction), $payload)
            ->assertSessionHasErrors([
                'items.0.restock' => 'The restock selection must be checked or unchecked.',
            ]);

        $this->assertDatabaseCount('refunds', 0);
    }

    public function test_cashier_cannot_view_refund_or_void_another_cashiers_sale(): void
    {
        $cashier = User::where('role', User::ROLE_CASHIER)->firstOrFail();
        $owner = User::create([
            'username' => 'other_cashier', 'name' => 'Other Cashier',
            'email' => 'other-cashier@example.test', 'password' => $cashier->password,
            'role' => User::ROLE_CASHIER, 'role_id' => $cashier->role_id, 'status' => 'active',
        ]);
        $this->transaction->update(['cashier_id' => $owner->id]);
        $this->actingAs($cashier);
        $stockBefore = $this->variant->inventoryStock->quantity_on_hand;

        $this->get(route('pos.receipt', $this->transaction))->assertForbidden();
        $this->get(route('pos.refund.form', $this->transaction))->assertForbidden();
        $this->post(route('pos.refund.process', $this->transaction), $this->refundPayload(1, 1))->assertForbidden();
        $this->post(route('pos.transactions.void', $this->transaction), [
            'void_reason' => 'Attempted unauthorized void',
        ])->assertForbidden();
        $this->assertDatabaseCount('refunds', 0);
        $this->assertDatabaseCount('refund_items', 0);
        $this->assertSame('completed', $this->transaction->fresh()->status);
        $this->assertSame($stockBefore, $this->variant->inventoryStock->fresh()->quantity_on_hand);
    }

    public function test_cashier_can_view_and_refund_their_own_sale(): void
    {
        $cashier = User::where('role', User::ROLE_CASHIER)->firstOrFail();
        $this->transaction->update(['cashier_id' => $cashier->id]);
        $this->actingAs($cashier);
        $this->get(route('pos.receipt', $this->transaction))->assertOk()->assertSee('Return Items');
        $this->get(route('pos.refund.form', $this->transaction))->assertOk();
        $this->post(route('pos.refund.process', $this->transaction), $this->refundPayload(1))
            ->assertRedirect(route('pos.receipt', $this->transaction))->assertSessionHasNoErrors();
        $this->assertSame('partial_refund', $this->transaction->fresh()->status);
    }

    public function test_cashier_can_void_their_own_sale(): void
    {
        $cashier = User::where('role', User::ROLE_CASHIER)->firstOrFail();
        $this->transaction->update(['cashier_id' => $cashier->id]);
        $this->actingAs($cashier);
        $stockBefore = (int) $this->variant->inventoryStock->quantity_on_hand;
        $this->post(route('pos.transactions.void', $this->transaction), [
            'void_reason' => 'Customer cancelled the purchase',
        ])->assertRedirect(route('pos.transactions'))->assertSessionHasNoErrors();
        $this->assertSame('voided', $this->transaction->fresh()->status);
        $this->assertSame($stockBefore + 5, (int) $this->variant->inventoryStock->fresh()->quantity_on_hand);
        $this->get(route('pos.receipt', $this->transaction))->assertOk()->assertDontSee('Return Items');
        $this->get(route('pos.transactions'))->assertOk()
            ->assertDontSee(route('pos.refund.form', $this->transaction));
    }

    public function test_admin_can_access_and_refund_a_cashiers_sale(): void
    {
        $cashier = User::where('role', User::ROLE_CASHIER)->firstOrFail();
        $this->transaction->update(['cashier_id' => $cashier->id]);
        $this->get(route('pos.receipt', $this->transaction))->assertOk();
        $this->get(route('pos.refund.form', $this->transaction))->assertOk();
        $this->post(route('pos.refund.process', $this->transaction), $this->refundPayload(1))
            ->assertRedirect(route('pos.receipt', $this->transaction))->assertSessionHasNoErrors();
    }

    public function test_duplicate_refund_items_cannot_inflate_stock(): void
    {
        $payload = $this->refundPayload(5, 1);
        $payload['items'][] = $payload['items'][0];
        $stockBefore = $this->variant->inventoryStock->quantity_on_hand;
        $this->post(route('pos.refund.process', $this->transaction), $payload)
            ->assertSessionHasErrors('items.0.transaction_item_id');
        $this->assertDatabaseCount('refunds', 0);
        $this->assertSame($stockBefore, $this->variant->inventoryStock->fresh()->quantity_on_hand);
    }

    public function test_voided_or_fully_refunded_sales_cannot_be_refunded(): void
    {
        foreach (['voided', 'refunded'] as $status) {
            $this->transaction->update(['status' => $status]);
            $this->get(route('pos.refund.form', $this->transaction))->assertStatus(409);
            $this->post(route('pos.refund.process', $this->transaction), $this->refundPayload(1))
                ->assertSessionHasErrors('items');
            $this->assertDatabaseCount('refunds', 0);
            $this->assertSame($status, $this->transaction->fresh()->status);
        }
    }

    public function test_refund_rejects_item_ids_from_another_transaction(): void
    {
        $otherSale = $this->transaction->replicate();
        $otherSale->receipt_no = 'RCP-OTHER-SALE';
        $otherSale->save();
        $otherItem = $this->transactionItem->replicate();
        $otherItem->transaction_id = $otherSale->id;
        $otherItem->save();
        $payload = $this->refundPayload(1);
        $payload['items'][0]['transaction_item_id'] = $otherItem->id;
        $this->post(route('pos.refund.process', $this->transaction), $payload)
            ->assertSessionHasErrors('items.0.transaction_item_id');
        $this->assertDatabaseCount('refunds', 0);
    }

    public function test_cashier_transaction_search_does_not_expose_other_sales(): void
    {
        $cashier = User::where('role', User::ROLE_CASHIER)->firstOrFail();
        $this->actingAs($cashier);
        $this->get(route('pos.transactions', ['search' => $this->transaction->receipt_no]))
            ->assertOk()->assertViewHas('transactions', fn ($rows) => $rows->total() === 0);
    }

    public function test_refund_failure_rolls_back_without_exposing_internal_errors(): void
    {
        $stockBefore = $this->variant->inventoryStock->quantity_on_hand;
        $this->mock(\App\Services\InventoryMonitoringService::class, function ($mock) {
            $mock->shouldReceive('recordMovement')->once()->andThrow(new \RuntimeException('Internal inventory failure'));
        });
        $this->post(route('pos.refund.process', $this->transaction), $this->refundPayload(1, 1))
            ->assertSessionHasErrors('error');
        $this->assertDatabaseCount('refunds', 0);
        $this->assertDatabaseCount('refund_items', 0);
        $this->assertSame('completed', $this->transaction->fresh()->status);
        $this->assertSame($stockBefore, $this->variant->inventoryStock->fresh()->quantity_on_hand);
    }

    private function refundPayload(int $quantity, int|string|null $restock = null): array
    {
        $item = [
            'transaction_item_id' => $this->transactionItem->id,
            'quantity' => $quantity,
        ];

        if ($restock !== null) {
            $item['restock'] = $restock;
        }

        return [
            'reason' => 'Test refund',
            'refund_method' => 'cash',
            'notes' => null,
            'items' => [$item],
        ];
    }
}
