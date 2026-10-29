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
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ProductPageUserExperienceTest extends TestCase
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
        $this->actingAs(User::where('role', User::ROLE_ADMIN)->firstOrFail());
    }

    public function test_products_page_displays_variant_identity_stock_and_actions(): void
    {
        $variant = ProductVariant::with(['product', 'inventoryStock'])->firstOrFail();

        $this->get(route('inventory.index', ['search' => $variant->variant_code]))
            ->assertOk()
            ->assertSee($variant->product->name)
            ->assertSee($variant->variant_code)
            ->assertSee('Product / Variant')
            ->assertSee('product-availability--'.$variant->status, false)
            ->assertSee(number_format($variant->inventoryStock?->quantity_on_hand ?? 0))
            ->assertSee(route('inventory.show', $variant), false)
            ->assertSee('Delete disabled for active variant '.$variant->variant_code)
            ->assertSee('Stock Overview')
            ->assertSee('data-live-filter', false)
            ->assertSee('data-live-results', false)
            ->assertDontSee('Clear filters')
            ->assertSee('Catalog &amp; pricing', false);
    }

    public function test_active_variant_cannot_be_deleted_directly(): void
    {
        $variant = ProductVariant::where('status', 'active')->firstOrFail();

        $this->from(route('inventory.index'))
            ->delete(route('inventory.destroy', $variant))
            ->assertRedirect(route('inventory.index'))
            ->assertSessionHasErrors('error');

        $this->assertDatabaseHas('product_variants', [
            'id' => $variant->id,
            'status' => 'active',
        ]);
    }

    public function test_products_page_status_filter_excludes_other_statuses(): void
    {
        $active = ProductVariant::where('status', 'active')->firstOrFail();
        $inactive = ProductVariant::whereKeyNot($active->id)->firstOrFail();
        $inactive->update(['status' => 'inactive']);

        $response = $this->get(route('inventory.index', ['status' => 'inactive']));

        $response->assertOk()
            ->assertSee($inactive->variant_code)
            ->assertDontSee($active->variant_code);
    }

    public function test_product_search_is_case_insensitive_even_with_case_sensitive_database_matching(): void
    {
        DB::statement('PRAGMA case_sensitive_like = ON');
        $variant = ProductVariant::with('product')->firstOrFail();
        $variant->product->update(['name' => 'Adapter Fitting']);

        foreach (['ada', 'ADA', 'aDaPtEr FiTtInG'] as $search) {
            $this->get(route('inventory.index', ['search' => $search]))
                ->assertOk()
                ->assertSee('Adapter Fitting');

            $this->getJson(route('pos.products', ['search' => $search]))
                ->assertOk()
                ->assertJsonFragment(['product_name' => 'Adapter Fitting']);
        }
    }
}
