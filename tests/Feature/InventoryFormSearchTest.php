<?php

namespace Tests\Feature;

use App\Http\Middleware\ValidateAuthorizedDevice;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InventoryFormSearchTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        $this->withoutMiddleware(ValidateAuthorizedDevice::class);
    }

    public function test_authorized_user_can_search_existing_products(): void
    {
        $this->actingAs(User::where('role', User::ROLE_ADMIN)->firstOrFail());
        $product = Product::where('status', 'active')->firstOrFail();

        $this->getJson(route('inventory.search.products', ['q' => $product->name]))
            ->assertOk()
            ->assertJsonFragment(['id' => $product->id, 'name' => $product->name])
            ->assertJsonMissingPath('data.0.description');
    }

    public function test_authorized_user_can_search_suppliers(): void
    {
        $this->actingAs(User::where('role', User::ROLE_ADMIN)->firstOrFail());
        $supplier = Supplier::where('status', 'active')->firstOrFail();

        $this->getJson(route('inventory.search.suppliers', ['q' => $supplier->name]))
            ->assertOk()
            ->assertJsonFragment(['id' => $supplier->id, 'name' => $supplier->name])
            ->assertJsonMissingPath('data.0.address');
    }

    public function test_inventory_form_search_is_validated_and_authenticated(): void
    {
        $this->getJson(route('inventory.search.products'))->assertUnauthorized();

        $this->actingAs(User::where('role', User::ROLE_ADMIN)->firstOrFail())
            ->getJson(route('inventory.search.products', ['q' => str_repeat('x', 101)]))
            ->assertUnprocessable()
            ->assertJsonValidationErrors('q');
    }
}
