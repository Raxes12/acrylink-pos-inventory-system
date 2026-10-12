<?php

namespace Tests\Feature;

use App\Http\Middleware\ValidateAuthorizedDevice;
use App\Models\ProductVariant;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StockReceiptSearchTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
        $this->withoutMiddleware(ValidateAuthorizedDevice::class);
    }

    public function test_authorized_staff_can_search_active_suppliers(): void
    {
        $this->actingAs(User::where('role', User::ROLE_ADMIN)->firstOrFail());
        $supplier = Supplier::where('status', 'active')->firstOrFail();

        $this->getJson(route('stock-receipts.search.suppliers', ['q' => $supplier->name]))
            ->assertOk()
            ->assertJsonPath('data.0.id', $supplier->id)
            ->assertJsonMissingPath('data.0.address');
    }

    public function test_authorized_staff_can_search_active_variants_by_identifiers(): void
    {
        $this->actingAs(User::where('role', User::ROLE_ADMIN)->firstOrFail());
        $variant = ProductVariant::with('product')->where('status', 'active')->firstOrFail();

        $this->getJson(route('stock-receipts.search.variants', ['q' => $variant->variant_code]))
            ->assertOk()
            ->assertJsonFragment(['id' => $variant->id, 'variant_code' => $variant->variant_code]);

        $this->getJson(route('stock-receipts.search.variants', ['q' => $variant->product->name]))
            ->assertOk()
            ->assertJsonFragment(['id' => $variant->id]);
    }

    public function test_search_parameters_are_validated(): void
    {
        $this->actingAs(User::where('role', User::ROLE_ADMIN)->firstOrFail());

        $this->getJson(route('stock-receipts.search.variants', ['q' => str_repeat('x', 101)]))
            ->assertUnprocessable()
            ->assertJsonValidationErrors('q');
    }

    public function test_stock_receipt_search_requires_authentication(): void
    {
        $this->getJson(route('stock-receipts.search.variants'))->assertUnauthorized();
        $this->getJson(route('stock-receipts.search.suppliers'))->assertUnauthorized();
    }
}
