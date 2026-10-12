<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\InventoryStock;
use App\Models\InventoryMovement;
use App\Models\IntrusionEvent;
use App\Models\ProductVariant;
use App\Models\SecurityRule;
use App\Models\StockAlert;
use App\Support\InputLimits;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\DB;

class InventoryMonitoringService
{
    private array $securityRules = [];

    public function recordMovement(ProductVariant|int $variant, string $type, int $quantityChange, ?string $referenceType = null, ?int $referenceId = null, ?string $remarks = null): InventoryMovement
    {
        return DB::transaction(function () use ($variant, $type, $quantityChange, $referenceType, $referenceId, $remarks) {
            $variant = $this->lockVariant($variant);
            $stock = InventoryStock::where('variant_id', $variant->id)->lockForUpdate()->first();

            if (!$stock) {
                $stock = InventoryStock::create([
                    'variant_id' => $variant->id,
                    'quantity_on_hand' => 0,
                    'minimum_stock' => 0,
                    'critical_stock' => 0,
                ]);
            }

            $quantityBefore = (int) $stock->quantity_on_hand;

            if (
                $quantityChange > InputLimits::DATABASE_INTEGER_MAX
                || $quantityChange < -InputLimits::DATABASE_INTEGER_MAX
                || ($quantityChange > 0 && $quantityBefore > InputLimits::DATABASE_INTEGER_MAX - $quantityChange)
            ) {
                throw ValidationException::withMessages([
                    'quantity' => 'The resulting stock quantity is too large. Enter a smaller quantity.',
                ]);
            }

            $quantityAfter = max(0, $quantityBefore + $quantityChange);
            $actualChange = $quantityAfter - $quantityBefore;

            $stock->update(['quantity_on_hand' => $quantityAfter]);

            $movement = InventoryMovement::create([
                'variant_id' => $variant->id,
                'user_id' => auth()->id(),
                'movement_type' => $type,
                'quantity_change' => $actualChange,
                'quantity_before' => $quantityBefore,
                'quantity_after' => $quantityAfter,
                'reference_type' => $referenceType,
                'reference_id' => $referenceId,
                'remarks' => $remarks,
                'created_at' => now(),
            ]);

            $variant->setRelation('inventoryStock', $stock->fresh());
            $this->syncStockAlert($variant);
            $this->inspectMovementForAnomalies($movement);

            return $movement;
        });
    }

    public function inspectMovementForAnomalies(InventoryMovement $movement): void
    {
        if (!$movement->variant_id) {
            return;
        }

        if ($movement->movement_type === 'sale') {
            $this->detectBulkInventoryChange($movement);
            return;
        }

        $movement->loadMissing('variant.product', 'variant.inventoryStock', 'user');
        if (!$movement->variant) {
            return;
        }

        $this->detectBulkInventoryChange($movement);
        $this->detectInventoryDiscrepancySpike($movement);
    }

    public function syncStockAlert(ProductVariant|int $variant): void
    {
        $variant = $this->variant($variant)->loadMissing('product');
        $stock = $variant->relationLoaded('inventoryStock')
            ? $variant->inventoryStock
            : InventoryStock::where('variant_id', $variant->id)->first();

        $quantity = (int) ($stock?->quantity_on_hand ?? 0);
        $minimum = (int) ($stock?->minimum_stock ?? 0);
        $critical = (int) ($stock?->critical_stock ?? max(0, floor($minimum / 2)));
        $label = ($variant->product?->name ?? 'Product variant').' ['.$variant->variant_code.']';

        if ($quantity <= 0) {
            $this->openAlert($variant, 'out_of_stock', "{$label} is out of stock.");
            return;
        }

        if ($quantity <= $critical) {
            $this->openAlert($variant, 'critical_stock', "{$label} is at critical stock level.");
            return;
        }

        if ($quantity <= $minimum) {
            $this->openAlert($variant, 'low_stock', "{$label} is below reorder level.");
            return;
        }

        StockAlert::where('variant_id', $variant->id)
            ->where('alert_status', 'open')
            ->update([
                'alert_status' => 'resolved',
                'resolved_at' => now(),
            ]);
    }

    private function openAlert(ProductVariant $variant, string $type, string $message): void
    {
        $alert = StockAlert::firstOrNew([
            'variant_id' => $variant->id,
            'alert_status' => 'open',
        ]);

        if (!$alert->exists) {
            $alert->created_at = now();
        }

        $alert->fill([
            'alert_type' => $type,
            'message' => $message,
        ])->save();
    }

    private function detectBulkInventoryChange(InventoryMovement $movement): void
    {
        $rule = $this->securityRule('bulk_inventory_change');

        if ($rule === false) {
            return;
        }

        $threshold = max(1, (int) ($rule?->thresholdOr(50) ?? 50));

        if (abs((int) $movement->quantity_change) < $threshold) {
            return;
        }

        $movement->loadMissing('variant.product', 'variant.inventoryStock', 'user');
        if (!$movement->variant) {
            return;
        }

        $description = $this->movementDescription(
            $movement,
            'Bulk inventory change detected for one product variant.'
        );

        $event = $this->raiseInventoryEvent(
            'bulk_inventory_change',
            $description,
            $movement,
            $rule instanceof SecurityRule ? $rule : null
        );

        $this->recordInventoryAnomalyAudit($movement, $event, $description);
    }

    private function detectInventoryDiscrepancySpike(InventoryMovement $movement): void
    {
        if ($movement->movement_type === 'sale') {
            return;
        }

        $ids = app(IntrusionDetectionService::class);
        $rule = $ids->ruleFor('inventory_discrepancy_spike');

        if ($rule === false) {
            return;
        }

        $window = max(1, $ids->ruleWindow('inventory_discrepancy_spike', 60));
        $threshold = max(1, (int) $ids->ruleThreshold('inventory_discrepancy_spike', 5));
        $recentMovementCount = InventoryMovement::where('variant_id', $movement->variant_id)
            ->where('movement_type', '!=', 'sale')
            ->where('created_at', '>=', now()->subMinutes($window))
            ->count();

        if ($recentMovementCount < $threshold) {
            return;
        }

        $recentSpike = IntrusionEvent::where('event_type', 'inventory_discrepancy_spike')
            ->where('rule_id', $rule instanceof SecurityRule ? $rule->security_rule_id : null)
            ->where('description', 'like', '%variant_code: '.($movement->variant?->variant_code ?? '').'.%')
            ->where('detected_at', '>=', now()->subMinutes($window))
            ->exists();
        if ($recentSpike) {
            return;
        }

        $description = $this->movementDescription(
            $movement,
            "{$recentMovementCount} inventory movement(s) were recorded for this variant within {$window} minute(s)."
        );

        $event = $this->raiseInventoryEvent(
            'inventory_discrepancy_spike',
            $description,
            $movement,
            $rule instanceof SecurityRule ? $rule : null
        );

        $this->recordInventoryAnomalyAudit($movement, $event, $description);
    }

    private function raiseInventoryEvent(string $type, string $description, InventoryMovement $movement, ?SecurityRule $rule): IntrusionEvent
    {
        $ids = app(IntrusionDetectionService::class);
        $severity = $ids->ruleSeverity($type, 'high');

        return $ids->createIntrusionEvent(
            $type,
            $severity,
            $description,
            request(),
            $movement->user,
            $rule,
            'inventory'
        );
    }

    private function movementDescription(InventoryMovement $movement, string $summary): string
    {
        $variant = $movement->variant;
        $user = $movement->user;
        $userLabel = $user?->username ?: $user?->name ?: 'system';

        return implode(' ', [
            $summary,
            'product_name: '.($variant?->product?->name ?? 'Deleted product').'.',
            'variant_code: '.($variant?->variant_code ?? 'unknown').'.',
            'part_number: '.($variant?->part_number ?: 'N/A').'.',
            'quantity_change: '.$movement->quantity_change.'.',
            'user: '.$userLabel.'.',
            'reference_type: '.($movement->reference_type ?: 'none').'.',
        ]);
    }

    private function recordInventoryAnomalyAudit(InventoryMovement $movement, IntrusionEvent $event, string $description): void
    {
        $stockId = $movement->variant?->inventoryStock?->id;

        AuditLog::record('IDS_INVENTORY', $description, $movement->user, [
            'affected_table' => $stockId ? 'inventory_stocks' : 'product_variants',
            'affected_record_id' => $stockId ?: $movement->variant_id,
            'new_values' => [
                'intrusion_event_id' => $event->id,
                'movement_id' => $movement->id,
                'variant_id' => $movement->variant_id,
                'movement_type' => $movement->movement_type,
                'quantity_change' => $movement->quantity_change,
                'quantity_before' => $movement->quantity_before,
                'quantity_after' => $movement->quantity_after,
                'reference_type' => $movement->reference_type,
                'reference_id' => $movement->reference_id,
            ],
        ]);
    }

    private function variant(ProductVariant|int $variant): ProductVariant
    {
        return $variant instanceof ProductVariant
            ? $variant
            : ProductVariant::findOrFail($variant);
    }

    private function lockVariant(ProductVariant|int $variant): ProductVariant
    {
        $variantId = $variant instanceof ProductVariant ? $variant->id : $variant;

        return ProductVariant::whereKey($variantId)->lockForUpdate()->firstOrFail();
    }

    private function securityRule(string $name): SecurityRule|false|null
    {
        if (!array_key_exists($name, $this->securityRules)) {
            $this->securityRules[$name] = app(IntrusionDetectionService::class)->ruleFor($name);
        }

        return $this->securityRules[$name];
    }
}
