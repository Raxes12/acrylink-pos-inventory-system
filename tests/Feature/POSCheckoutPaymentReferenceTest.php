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
use App\Models\ProductCategory;
use App\Models\ProductVariant;
use App\Models\Supplier;
use App\Models\Transaction;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class POSCheckoutPaymentReferenceTest extends TestCase
{
    use RefreshDatabase;

    private ProductVariant $variant;

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
        $this->actingAs(User::where('role', User::ROLE_CASHIER)->firstOrFail());
        $this->variant = ProductVariant::with('product', 'inventoryStock')->firstOrFail();
    }

    public function test_cash_checkout_still_works_without_payment_reference(): void
    {
        $startingStock = (int) $this->variant->inventoryStock->quantity_on_hand;
        $unitPrice = (float) $this->variant->selling_price;

        $response = $this->post(route('pos.checkout'), $this->checkoutPayload('cash'));

        $transaction = Transaction::latest('id')->firstOrFail();

        $response->assertRedirect(route('pos.receipt', $transaction));
        $this->assertNull($transaction->payment_reference);

        $this->assertDatabaseHas('transaction_items', [
            'transaction_id' => $transaction->id,
            'product_id' => $this->variant->product_id,
            'variant_id' => $this->variant->id,
            'product_name_snapshot' => $this->variant->product->name,
            'part_number_snapshot' => $this->variant->part_number,
            'size_snapshot' => $this->variant->size,
            'color_snapshot' => $this->variant->color,
            'unit_snapshot' => $this->variant->unit?->code ?? 'pcs',
            'quantity' => 1,
            'unit_price' => number_format($unitPrice, 2, '.', ''),
            'subtotal' => number_format($unitPrice, 2, '.', ''),
        ]);

        $this->assertDatabaseHas('inventory_stocks', [
            'variant_id' => $this->variant->id,
            'quantity_on_hand' => $startingStock - 1,
        ]);

        $this->assertDatabaseHas('inventory_movements', [
            'variant_id' => $this->variant->id,
            'movement_type' => 'sale',
            'quantity_change' => -1,
            'quantity_before' => $startingStock,
            'quantity_after' => $startingStock - 1,
            'reference_type' => 'sale',
            'reference_id' => $transaction->id,
        ]);
    }

    public function test_checkout_applies_variant_discount_and_keeps_receipt_discount_aggregate(): void
    {
        $unitPrice = (float) $this->variant->selling_price;
        $lineDiscount = min(10.0, max(0.01, $unitPrice / 10));
        $payment = round($unitPrice - $lineDiscount, 2);

        $response = $this->post(route('pos.checkout'), $this->checkoutPayload('cash', null, [
            'items' => [[
                'variant_id' => $this->variant->id,
                'quantity' => 1,
                'unit_price' => $unitPrice,
                'discount' => $lineDiscount,
            ]],
            'discount' => 0,
            'payment' => $payment,
        ]));

        $transaction = Transaction::with('items')->latest('id')->firstOrFail();
        $response->assertRedirect(route('pos.receipt', $transaction));
        $this->assertSame(number_format($lineDiscount, 2, '.', ''), $transaction->discount);
        $this->assertSame(number_format($payment, 2, '.', ''), $transaction->total);
        $this->assertSame(number_format($lineDiscount, 2, '.', ''), $transaction->items->first()->discount);
        $this->assertSame($payment, $transaction->items->first()->netLineTotal());

        $this->get(route('pos.receipt', $transaction))
            ->assertOk()
            ->assertSee('Item discount:')
            ->assertSee(number_format($lineDiscount, 2));
    }

    public function test_checkout_uses_the_agreed_cart_price_instead_of_the_catalogue_price(): void
    {
        $agreedPrice = 2000.00;

        $response = $this->post(route('pos.checkout'), $this->checkoutPayload('cash', null, [
            'items' => [[
                'variant_id' => $this->variant->id,
                'quantity' => 1,
                'unit_price' => $agreedPrice,
            ]],
            'payment' => $agreedPrice,
        ]));

        $transaction = Transaction::with('items')->latest('id')->firstOrFail();

        $response->assertRedirect(route('pos.receipt', $transaction));
        $this->assertSame('2000.00', $transaction->subtotal);
        $this->assertSame('2000.00', $transaction->total);
        $this->assertSame('2000.00', $transaction->items->first()->unit_price);
        $this->assertSame('2000.00', $transaction->items->first()->subtotal);
        $this->assertSame(number_format((float) $this->variant->selling_price, 2, '.', ''), $this->variant->fresh()->selling_price);
    }

    public function test_checkout_records_the_sales_associate_separately_from_the_cashier(): void
    {
        $response = $this->post(route('pos.checkout'), $this->checkoutPayload('cash', null, [
            'sales_associate' => 'Maria Santos',
        ]));

        $transaction = Transaction::latest('id')->firstOrFail();

        $response->assertRedirect(route('pos.receipt', $transaction));
        $this->assertSame('Maria Santos', $transaction->sales_associate);
        $this->get(route('pos.receipt', $transaction))
            ->assertOk()
            ->assertSee('Sales assisted by Maria Santos');
        $this->get(route('pos.transactions', ['search' => 'maria santos']))
            ->assertOk()
            ->assertSee('Sales associate')
            ->assertSee('Maria Santos');
    }

    public function test_checkout_rejects_a_variant_discount_above_its_line_subtotal(): void
    {
        $unitPrice = (float) $this->variant->selling_price;

        $this->post(route('pos.checkout'), $this->checkoutPayload('cash', null, [
            'items' => [[
                'variant_id' => $this->variant->id,
                'quantity' => 1,
                'unit_price' => $unitPrice,
                'discount' => $unitPrice + 0.01,
            ]],
        ]))->assertSessionHasErrors('items.0.discount');

        $this->assertDatabaseCount('transactions', 0);
    }

    public function test_repeated_checkout_token_returns_existing_sale_without_deducting_stock_twice(): void
    {
        $token = (string) Str::uuid();
        $startingStock = (int) $this->variant->inventoryStock->quantity_on_hand;
        $payload = $this->checkoutPayload('cash', null, ['checkout_token' => $token]);

        $firstResponse = $this->post(route('pos.checkout'), $payload);
        $transaction = Transaction::where('checkout_token', $token)->firstOrFail();
        $firstResponse->assertRedirect(route('pos.receipt', $transaction));

        $secondResponse = $this->post(route('pos.checkout'), $payload);
        $secondResponse->assertRedirect(route('pos.receipt', $transaction));

        $this->assertSame(1, Transaction::where('checkout_token', $token)->count());
        $this->assertSame(
            $startingStock - 1,
            (int) $this->variant->fresh('inventoryStock')->inventoryStock->quantity_on_hand
        );
    }

    public function test_pos_page_uses_database_variant_payload(): void
    {
        $this->variant->update([
            'part_number' => 'AF-2048',
            'size' => 'Standard',
            'color' => 'White',
        ]);

        $this->get(route('pos.index', ['search' => $this->variant->variant_code]))
            ->assertOk()
            ->assertSee('id="posProducts"', false)
            ->assertDontSee('id="posVariants"', false)
            ->assertSee('id="posSearchClear"', false)
            ->assertDontSee('id="posCategoryFilter"', false)
            ->assertSee('enterkeyhint="search"', false)
            ->assertSee('id="cartCount"', false)
            ->assertSee('Your cart is empty.')
            ->assertSee('Select a product to begin a sale.')
            ->assertSee('id="amountDue"', false)
            ->assertSee('id="checkoutFeedback"', false)
            ->assertSee($this->variant->variant_code)
            ->assertSee($this->variant->product->name)
            ->assertSee('Part No.')
            ->assertSee('AF-2048')
            ->assertSee('Standard')
            ->assertSee('White')
            ->assertSee('Variant identifying details')
            ->assertSee('Credit')
            ->assertSee('Check')
            ->assertDontSee('Type / Size');
    }

    public function test_pos_product_endpoint_finds_an_exact_barcode(): void
    {
        $this->variant->update(['barcode' => '8999900012345']);

        $this->getJson(route('pos.products', ['search' => $this->variant->barcode]))
            ->assertOk()
            ->assertJsonFragment([
                'id' => $this->variant->id,
                'barcode' => '8999900012345',
            ]);
    }

    public function test_pos_distinguishes_inventory_search_and_category_empty_states(): void
    {
        $this->get(route('pos.index', ['search' => '__NO_POS_MATCH__']))
            ->assertOk()
            ->assertSee('No search results')
            ->assertSee('No products match');

        ProductCategory::create(['name' => 'Empty POS Category']);
        $this->get(route('pos.index', ['category' => 'Empty POS Category']))
            ->assertOk()
            ->assertSee('Search for a product')
            ->assertDontSee('<article class="product-card ', false);

        ProductVariant::query()->update(['status' => 'inactive']);
        $this->get(route('pos.index', ['search' => $this->variant->variant_code]))
            ->assertOk()
            ->assertSee('No search results')
            ->assertSee('No products match');
    }

    public function test_pos_page_paginates_initial_variant_payload(): void
    {
        $product = Product::create([
            'name' => 'ZZZ POS Pagination Product',
            'category' => ProductCategory::firstOrFail()->name,
            'description' => 'POS pagination hidden description.',
            'status' => 'active',
        ]);
        $unit = Unit::firstOrFail();

        foreach (range(1, 35) as $index) {
            $variant = ProductVariant::create([
                'product_id' => $product->id,
                'unit_id' => $unit->id,
                'variant_code' => sprintf('ZZZ-POS-PAGE-%03d', $index),
                'selling_price' => 100 + $index,
                'cost_price' => 50,
                'status' => 'active',
            ]);

            InventoryStock::create([
                'variant_id' => $variant->id,
                'quantity_on_hand' => 10,
                'minimum_stock' => 3,
                'critical_stock' => 1,
            ]);
        }

        $response = $this->get(route('pos.index', ['search' => 'ZZZ POS Pagination Product']));

        $response->assertOk()
            ->assertViewHas('posVariants', fn ($variants) => $variants->perPage() === 30
                && $variants->count() === 30
                && $variants->total() > 30);

        $this->assertSame(30, substr_count($response->getContent(), '<article class="product-card '));
    }

    public function test_pos_does_not_load_product_cards_until_a_search_is_entered(): void
    {
        $this->get(route('pos.index'))
            ->assertOk()
            ->assertSee('Search for a product')
            ->assertViewHas('posVariants', fn ($variants) => $variants->total() === 0)
            ->assertDontSee('<article class="product-card ', false);

        $this->getJson(route('pos.products'))
            ->assertOk()
            ->assertJsonPath('data', [])
            ->assertJsonPath('has_more', false)
            ->assertJsonPath('next_page', null);
    }

    public function test_pos_initial_payload_excludes_modal_only_details(): void
    {
        $supplier = Supplier::create([
            'name' => 'POS Hidden Supplier',
            'status' => 'active',
            'payment_method' => 'cash',
        ]);
        $product = Product::create([
            'name' => 'AAA POS Detail Product',
            'category' => ProductCategory::firstOrFail()->name,
            'description' => 'POS Hidden Description Only',
            'status' => 'active',
        ]);
        $variant = ProductVariant::create([
            'product_id' => $product->id,
            'unit_id' => Unit::firstOrFail()->id,
            'supplier_id' => $supplier->id,
            'variant_code' => 'AAA-POS-DETAIL-001',
            'part_number' => 'POS-DETAIL-PN',
            'image_path' => 'product-variants/hidden.webp',
            'selling_price' => 125,
            'cost_price' => 70,
            'status' => 'active',
        ]);
        InventoryStock::create([
            'variant_id' => $variant->id,
            'quantity_on_hand' => 8,
            'minimum_stock' => 3,
            'critical_stock' => 1,
        ]);

        $this->get(route('pos.index', ['search' => 'AAA-POS-DETAIL-001']))
            ->assertOk()
            ->assertSee('AAA-POS-DETAIL-001')
            ->assertSee('data-info-url=', false)
            ->assertSee(asset('storage/product-variants/hidden.webp'))
            ->assertSee('width="800" height="450" loading="eager" decoding="async"', false)
            ->assertSee('data-variant-image-placeholder', false)
            ->assertDontSee('POS Hidden Description Only')
            ->assertDontSee('POS Hidden Supplier');

        $this->getJson(route('pos.variants.details', $variant))
            ->assertOk()
            ->assertJsonPath('description', 'POS Hidden Description Only')
            ->assertJsonPath('supplier', 'POS Hidden Supplier')
            ->assertJsonPath('barcode', null)
            ->assertJsonPath('size', null)
            ->assertJsonPath('color', null)
            ->assertJsonPath('image_url', asset('storage/product-variants/hidden.webp'))
            ->assertJsonMissingPath('image_path')
            ->assertJsonMissingPath('product_id')
            ->assertJsonMissingPath('details_url');
    }

    public function test_pos_variant_details_reject_inactive_and_missing_variants(): void
    {
        $this->variant->update(['status' => 'inactive']);

        $this->getJson(route('pos.variants.details', $this->variant))->assertNotFound();
        $this->getJson('/pos/variants/999999/details')->assertNotFound();
    }

    public function test_pos_variant_details_reject_inactive_products(): void
    {
        $this->variant->product->update(['status' => 'inactive']);

        $this->getJson(route('pos.variants.details', $this->variant))->assertNotFound();
    }

    public function test_pos_variant_details_require_authentication(): void
    {
        auth()->logout();

        $this->getJson(route('pos.variants.details', $this->variant))->assertUnauthorized();
    }

    public function test_gcash_checkout_requires_payment_reference(): void
    {
        $this->post(route('pos.checkout'), $this->checkoutPayload('gcash'))
            ->assertSessionHasErrors('payment_reference');

        $this->assertDatabaseCount('transactions', 0);
    }

    public function test_cash_checkout_rejects_insufficient_payment(): void
    {
        $this->post(route('pos.checkout'), $this->checkoutPayload('cash', null, [
            'payment' => max(0, (float) $this->variant->selling_price - 1),
        ]))->assertSessionHasErrors('payment');

        $this->assertDatabaseCount('transactions', 0);
    }

    public function test_checkout_rejects_negative_discount(): void
    {
        $this->post(route('pos.checkout'), $this->checkoutPayload('cash', null, [
            'discount' => -1,
        ]))->assertSessionHasErrors('discount');

        $this->assertDatabaseCount('transactions', 0);
    }

    public function test_checkout_rejects_oversized_numeric_inputs(): void
    {
        $startingStock = (int) $this->variant->inventoryStock->quantity_on_hand;

        $this->post(route('pos.checkout'), $this->checkoutPayload('cash', null, [
            'discount' => '999999999999999999',
            'payment' => '999999999999999999',
        ]))->assertSessionHasErrors(['discount', 'payment']);

        $this->assertDatabaseCount('transactions', 0);
        $this->assertSame($startingStock, (int) $this->variant->fresh('inventoryStock')->inventoryStock->quantity_on_hand);
    }

    public function test_checkout_rejects_an_empty_cart(): void
    {
        $this->post(route('pos.checkout'), $this->checkoutPayload('cash', null, [
            'items' => [],
        ]))->assertSessionHasErrors('items');

        $this->assertDatabaseCount('transactions', 0);
    }

    public function test_gcash_reference_cannot_exceed_one_hundred_characters(): void
    {
        $this->post(route('pos.checkout'), $this->checkoutPayload('gcash', str_repeat('1', 101)))
            ->assertSessionHasErrors('payment_reference');

        $this->assertDatabaseCount('transactions', 0);
    }

    public function test_gcash_reference_is_stored_and_displayed_on_receipt(): void
    {
        $reference = 'GCASH-REF-123456';

        $response = $this->post(route('pos.checkout'), $this->checkoutPayload('gcash', $reference));
        $transaction = Transaction::latest('id')->firstOrFail();

        $response->assertRedirect(route('pos.receipt', $transaction));
        $this->assertSame($reference, $transaction->payment_reference);

        $this->get(route('pos.receipt', $transaction))
            ->assertOk()
            ->assertSee('GCash Reference:')
            ->assertSee($reference)
            ->assertSee($this->variant->product->name)
            ->assertSee('Unit: '.$this->variant->unit?->code);
    }

    public function test_retired_card_checkout_is_rejected(): void
    {
        $reference = 'CARD-AUTH-00991';

        $response = $this->post(route('pos.checkout'), $this->checkoutPayload('credit', $reference, [
            'payment_card_type' => 'credit',
            'payment_card_last4' => '4421',
            'payment_payer_name' => 'Juan Dela Cruz',
        ]));
        $response->assertSessionHasErrors('payment_method');
        $this->assertDatabaseCount('transactions', 0);
    }

    public function test_retired_card_method_fails_before_card_detail_validation(): void
    {
        $this->post(route('pos.checkout'), $this->checkoutPayload('credit'))
            ->assertSessionHasErrors('payment_method');

        $this->assertDatabaseCount('transactions', 0);
    }

    public function test_check_checkout_stores_check_details(): void
    {
        $response = $this->post(route('pos.checkout'), $this->checkoutPayload('check', null, [
            'check_number' => 'CHK-1001',
            'check_bank' => 'BDO',
            'check_date' => now()->toDateString(),
        ]));
        $transaction = Transaction::latest('id')->firstOrFail();

        $response->assertRedirect(route('pos.receipt', $transaction));
        $this->assertSame('check', $transaction->payment_method);
        $this->assertSame('CHK-1001', $transaction->check_number);
        $this->assertSame('BDO', $transaction->check_bank);
        $payment = $transaction->payments()->with('paymentMethod')->sole();
        $this->assertSame('check', $payment->paymentMethod->code);
        $this->assertSame('CHK-1001', $payment->check_number);
        $this->assertSame('BDO', $payment->check_bank);
        $this->assertSame(now()->toDateString(), $payment->check_date->toDateString());

        $this->get(route('pos.receipt', $transaction))
            ->assertOk()
            ->assertSee('Check Details')
            ->assertSee('CHK-1001')
            ->assertSee('BDO');
    }

    public function test_check_checkout_requires_check_details(): void
    {
        $this->post(route('pos.checkout'), $this->checkoutPayload('check'))
            ->assertSessionHasErrors(['check_number', 'check_bank', 'check_date']);

        $this->assertDatabaseCount('transactions', 0);
    }

    private function checkoutPayload(string $paymentMethod, ?string $reference = null, array $overrides = []): array
    {
        return array_merge([
            'items' => [[
                'variant_id' => $this->variant->id,
                'quantity' => 1,
                'unit_price' => $this->variant->selling_price,
            ]],
            'discount' => 0,
            'payment_method' => $paymentMethod,
            'payment_reference' => $reference,
            'payment' => $this->variant->selling_price,
        ], $overrides);
    }
}
