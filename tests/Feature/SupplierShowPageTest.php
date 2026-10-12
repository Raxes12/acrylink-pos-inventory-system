<?php

namespace Tests\Feature;

use App\Http\Middleware\CheckBlockedEntity;
use App\Http\Middleware\DenyBlockedDevice;
use App\Http\Middleware\DetectInputAttack;
use App\Http\Middleware\DetectSessionAnomaly;
use App\Http\Middleware\TrackUserSession;
use App\Http\Middleware\ValidateAuthorizedDevice;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SupplierShowPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_supplier_details_and_linked_variant_render(): void
    {
        $this->seed();
        $this->withoutMiddleware([
            CheckBlockedEntity::class,
            DenyBlockedDevice::class,
            DetectInputAttack::class,
            DetectSessionAnomaly::class,
            TrackUserSession::class,
            ValidateAuthorizedDevice::class,
        ]);
        $this->actingAs(User::where('role', User::ROLE_ADMIN)->firstOrFail());

        $variant = ProductVariant::with(['supplier', 'brand', 'inventoryStock'])
            ->whereNotNull('supplier_id')
            ->firstOrFail();
        $supplier = $variant->supplier;

        $this->get(route('suppliers.show', $supplier))
            ->assertOk()
            ->assertSee($supplier->name)
            ->assertSee('Supplier details')
            ->assertSee('Supplied products')
            ->assertSee($variant->variant_code)
            ->assertSee($variant->brand?->name ?? '-')
            ->assertSee('&#8369;'.number_format((float) $variant->selling_price, 2), false)
            ->assertSee(route('inventory.show', $variant), false);
    }
}
