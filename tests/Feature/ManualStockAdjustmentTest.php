<?php

namespace Tests\Feature;

use App\Http\Middleware\CheckBlockedEntity;
use App\Http\Middleware\DenyBlockedDevice;
use App\Http\Middleware\DetectInputAttack;
use App\Http\Middleware\DetectSessionAnomaly;
use App\Http\Middleware\TrackUserSession;
use App\Http\Middleware\ValidateAuthorizedDevice;
use App\Models\AuditLog;
use App\Models\InventoryMovement;
use App\Models\ProductVariant;
use App\Models\SecurityRule;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ManualStockAdjustmentTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

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
        SecurityRule::whereIn('rule_name', ['bulk_inventory_change', 'inventory_discrepancy_spike'])
            ->update(['is_active' => false]);
        $this->admin = User::where('role', User::ROLE_ADMIN)->firstOrFail();
        $this->actingAs($this->admin);
    }

    public function test_stock_increase_saves_reason_movement_and_audit_details(): void
    {
        $variant = $this->variantWithStock(20);

        $this->post(route('inventory.adjust-stock', $variant), $this->payload([
            'type' => 'stock_in',
            'reason' => 'found_stock',
            'quantity' => 5,
            'remarks' => 'Five units found during shelf count.',
        ]))->assertRedirect()->assertSessionHasNoErrors();

        $this->assertSame(25, (int) $variant->fresh('inventoryStock')->inventoryStock->quantity_on_hand);
        $this->assertDatabaseHas('inventory_movements', [
            'variant_id' => $variant->id,
            'movement_type' => 'stock_in',
            'adjustment_reason' => 'found_stock',
            'quantity_change' => 5,
            'quantity_before' => 20,
            'quantity_after' => 25,
            'remarks' => 'Five units found during shelf count.',
        ]);

        $audit = AuditLog::where('action', 'INVENTORY_ADJUSTMENT')->latest('id')->firstOrFail();
        $this->assertSame(20, $audit->old_values['quantity_on_hand']);
        $this->assertSame(25, $audit->new_values['quantity_on_hand']);
        $this->assertSame(5, $audit->new_values['quantity_change']);
        $this->assertSame('Found Stock', $audit->new_values['adjustment_reason_label']);

        $this->get(route('inventory.movements'))
            ->assertOk()
            ->assertSee('Stock Increase (+)')
            ->assertSee('Found Stock')
            ->assertSee('Five units found during shelf count.');
    }

    public function test_stock_decrease_uses_latest_stock_and_rejects_negative_result(): void
    {
        $variant = $this->variantWithStock(8);

        $this->post(route('inventory.adjust-stock', $variant), $this->payload([
            'type' => 'stock_out',
            'reason' => 'damaged',
            'quantity' => 3,
        ]))->assertSessionHasNoErrors();

        $this->assertSame(5, (int) $variant->fresh('inventoryStock')->inventoryStock->quantity_on_hand);

        $movementCount = InventoryMovement::where('variant_id', $variant->id)->count();
        $this->post(route('inventory.adjust-stock', $variant), $this->payload([
            'type' => 'stock_out',
            'reason' => 'damaged',
            'quantity' => 6,
            'adjustment_token' => '00000000-0000-4000-8000-000000000302',
        ]))->assertSessionHasErrors([
            'quantity' => 'Insufficient stock. The adjustment quantity exceeds the available inventory.',
        ]);

        $this->assertSame(5, (int) $variant->fresh('inventoryStock')->inventoryStock->quantity_on_hand);
        $this->assertSame($movementCount, InventoryMovement::where('variant_id', $variant->id)->count());
    }

    public function test_adjustment_validation_rejects_invalid_combinations_and_missing_required_remarks(): void
    {
        $variant = $this->variantWithStock(10);
        $startingMovements = InventoryMovement::where('variant_id', $variant->id)->count();
        $cases = [
            [['quantity' => ''], 'quantity'],
            [['quantity' => 0], 'quantity'],
            [['quantity' => 1.5], 'quantity'],
            [['type' => 'adjustment'], 'type'],
            [['reason' => 'not_a_reason'], 'reason'],
            [['type' => 'stock_in', 'reason' => 'damaged'], 'reason'],
            [['reason' => 'other', 'remarks' => ''], 'remarks'],
            [['type' => 'stock_out', 'reason' => 'lost_missing', 'remarks' => ''], 'remarks'],
        ];

        foreach ($cases as $index => [$overrides, $field]) {
            $this->post(route('inventory.adjust-stock', $variant), $this->payload(array_merge($overrides, [
                'adjustment_token' => sprintf('00000000-0000-4000-8000-%012d', 400 + $index),
            ])))->assertSessionHasErrors($field);
        }

        $this->assertSame(10, (int) $variant->fresh('inventoryStock')->inventoryStock->quantity_on_hand);
        $this->assertSame($startingMovements, InventoryMovement::where('variant_id', $variant->id)->count());
        $this->assertDatabaseMissing('audit_logs', ['action' => 'INVENTORY_ADJUSTMENT']);
    }

    public function test_duplicate_confirmation_token_only_applies_adjustment_once(): void
    {
        $variant = $this->variantWithStock(10);
        $payload = $this->payload([
            'quantity' => 2,
            'adjustment_token' => '00000000-0000-4000-8000-000000000500',
        ]);

        $this->post(route('inventory.adjust-stock', $variant), $payload)->assertSessionHasNoErrors();
        $this->post(route('inventory.adjust-stock', $variant), $payload)
            ->assertSessionHasNoErrors()
            ->assertSessionHas('success', 'This stock adjustment was already applied. No duplicate change was made.');

        $this->assertSame(12, (int) $variant->fresh('inventoryStock')->inventoryStock->quantity_on_hand);
        $this->assertSame(1, InventoryMovement::where('adjustment_token', $payload['adjustment_token'])->count());
        $this->assertSame(1, AuditLog::where('action', 'INVENTORY_ADJUSTMENT')
            ->where('affected_record_id', (string) $variant->inventoryStock->id)
            ->count());
    }

    public function test_stock_overview_exposes_dynamic_reasons_preview_confirmation_and_accessibility(): void
    {
        $this->get(route('inventory.stock-overview'))
            ->assertOk()
            ->assertSee('Manual Stock Adjustment')
            ->assertSee('Stock Increase (+)')
            ->assertSee('Stock Decrease (-)')
            ->assertDontSee('Other Increase')
            ->assertSee('Reason for Adjustment')
            ->assertSee('Stock Adjustment Preview')
            ->assertSee('Confirm Stock Adjustment')
            ->assertSee('aria-modal="true"', false)
            ->assertSee('data-review-adjustment', false)
            ->assertSee('data-confirm-adjustment', false)
            ->assertSee('Provide additional details about this stock adjustment...');
    }

    public function test_historical_movements_without_adjustment_reason_remain_readable(): void
    {
        $variant = $this->variantWithStock(10);
        InventoryMovement::create([
            'variant_id' => $variant->id,
            'user_id' => $this->admin->id,
            'movement_type' => 'stock_in',
            'quantity_change' => 2,
            'quantity_before' => 8,
            'quantity_after' => 10,
            'reference_type' => 'legacy_import',
            'reference_id' => $variant->id,
            'remarks' => 'Legacy movement without a reason.',
            'created_at' => now(),
        ]);

        $this->assertTrue(Schema::hasColumns('inventory_movements', ['adjustment_reason', 'adjustment_token']));
        $this->get(route('inventory.movements'))
            ->assertOk()
            ->assertSee('Legacy movement without a reason.')
            ->assertSee('Stock In');
    }

    private function variantWithStock(int $quantity): ProductVariant
    {
        $variant = ProductVariant::with('inventoryStock')->firstOrFail();
        $variant->inventoryStock->update([
            'quantity_on_hand' => $quantity,
            'minimum_stock' => 5,
            'critical_stock' => 2,
        ]);

        return $variant;
    }

    private function payload(array $overrides = []): array
    {
        return array_merge([
            'type' => 'stock_in',
            'reason' => 'inventory_count_correction',
            'quantity' => 1,
            'remarks' => null,
            'adjustment_token' => '00000000-0000-4000-8000-000000000301',
        ], $overrides);
    }
}
