<?php

namespace Tests\Feature;

use App\Http\Middleware\ValidateAuthorizedDevice;
use App\Models\AuditLog;
use App\Models\AuthorizedDevice;
use App\Models\IntrusionEvent;
use App\Models\InventoryMovement;
use App\Models\InventoryStock;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductVariant;
use App\Models\SecurityRule;
use App\Models\StockReceipt;
use App\Models\Supplier;
use App\Models\User;
use App\Services\IntrusionDetectionService;
use App\Services\SystemSettingsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CrudUpdateTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
        $this->withoutMiddleware(ValidateAuthorizedDevice::class);
        $this->actingAs(User::where('role', User::ROLE_ADMIN)->firstOrFail());
    }

    public function test_product_can_be_updated(): void
    {
        $variant = ProductVariant::with('product', 'inventoryStock')->firstOrFail();
        $product = $variant->product;
        $supplier = Supplier::firstOrFail();

        $this->put(route('inventory.update', $variant), [
            'product_id' => $product->id,
            'code' => $variant->variant_code,
            'barcode' => '',
            'part_number' => 'TEST-PN-001',
            'name' => 'Updated Test Product',
            'brand' => '',
            'size' => 'Standard',
            'color' => 'Black',
            'category' => 'Engine Components',
            'price' => 999.50,
            'cost' => 500,
            'stock' => 12,
            'min_stock' => 3,
            'critical_stock' => 1,
            'unit' => 'PCS',
            'supplier_id' => $supplier->id,
            'status' => 'active',
        ])->assertRedirect(route('inventory.index'))
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'name' => 'Updated Test Product',
        ]);

        $this->assertDatabaseHas('product_variants', [
            'id' => $variant->id,
            'product_id' => $product->id,
            'variant_code' => $variant->variant_code,
            'part_number' => 'TEST-PN-001',
            'size' => 'Standard',
            'color' => 'Black',
            'selling_price' => '999.50',
            'cost_price' => '500.00',
        ]);

        $this->assertDatabaseHas('inventory_stocks', [
            'variant_id' => $variant->id,
            'quantity_on_hand' => 12,
            'minimum_stock' => 3,
            'critical_stock' => 1,
        ]);
    }

    public function test_inventory_update_rejects_values_larger_than_database_columns(): void
    {
        $variant = ProductVariant::with('product', 'inventoryStock')->firstOrFail();
        $stockBefore = (int) $variant->inventoryStock->quantity_on_hand;

        $this->put(route('inventory.update', $variant), [
            'product_id' => $variant->product_id,
            'code' => $variant->variant_code,
            'name' => $variant->product->name,
            'category' => $variant->product->category,
            'price' => 100000000,
            'cost' => 100000000,
            'stock' => '999999999999999999',
            'min_stock' => '999999999999999999',
            'critical_stock' => '999999999999999999',
            'unit' => $variant->unit?->code ?? 'PCS',
            'status' => $variant->status,
        ])->assertSessionHasErrors(['price', 'cost', 'stock', 'min_stock', 'critical_stock']);

        $this->assertSame($stockBefore, (int) $variant->fresh('inventoryStock')->inventoryStock->quantity_on_hand);
    }

    public function test_stock_adjustment_rejects_a_result_that_would_overflow(): void
    {
        $variant = ProductVariant::with('inventoryStock')->firstOrFail();
        $variant->inventoryStock->update(['quantity_on_hand' => 10]);

        $this->post(route('inventory.adjust-stock', $variant), [
            'type' => 'stock_in',
            'quantity' => 2147483647,
            'remarks' => 'Boundary validation test.',
        ])->assertSessionHasErrors('quantity');

        $this->assertSame(10, (int) $variant->fresh('inventoryStock')->inventoryStock->quantity_on_hand);
    }

    public function test_stock_receipt_rejects_a_line_total_that_would_overflow(): void
    {
        $variant = ProductVariant::with('inventoryStock')->firstOrFail();
        $stockBefore = (int) $variant->inventoryStock->quantity_on_hand;

        $this->post(route('stock-receipts.store'), [
            'date_received' => now()->toDateString(),
            'items' => [[
                'variant_id' => $variant->id,
                'quantity_received' => 2,
                'unit_cost' => 99999999.99,
            ]],
        ])->assertSessionHasErrors('items.0.unit_cost');

        $this->assertDatabaseCount('stock_receipts', 0);
        $this->assertSame($stockBefore, (int) $variant->fresh('inventoryStock')->inventoryStock->quantity_on_hand);
    }

    public function test_inventory_can_create_another_variant_under_existing_product(): void
    {
        $product = Product::firstOrFail();
        $supplier = Supplier::firstOrFail();

        $this->post(route('inventory.store'), [
            'product_id' => $product->id,
            'code' => 'AP-MULTI-001',
            'barcode' => 'TEST-BARCODE-001',
            'part_number' => 'ALT-PN-001',
            'brand' => 'Variant Test Brand',
            'size' => 'Large',
            'color' => 'Blue',
            'price' => 1500,
            'cost' => 900,
            'stock' => 8,
            'min_stock' => 3,
            'critical_stock' => 1,
            'unit' => 'PCS',
            'supplier_id' => $supplier->id,
            'status' => 'active',
        ])->assertRedirect(route('inventory.index'))
            ->assertSessionHasNoErrors();

        $variant = ProductVariant::where('variant_code', 'AP-MULTI-001')->firstOrFail();

        $this->assertSame($product->id, $variant->product_id);
        $this->assertDatabaseHas('inventory_stocks', [
            'variant_id' => $variant->id,
            'quantity_on_hand' => 8,
            'minimum_stock' => 3,
            'critical_stock' => 1,
        ]);
        $this->assertDatabaseHas('inventory_movements', [
            'variant_id' => $variant->id,
            'movement_type' => 'stock_in',
            'quantity_before' => 0,
            'quantity_after' => 8,
        ]);
    }

    public function test_supplier_can_be_updated(): void
    {
        $supplier = Supplier::firstOrFail();

        $this->put(route('suppliers.update', $supplier), [
            'name' => 'Updated Supplier',
            'contact' => 'New Contact',
            'phone' => '09170000000',
            'email' => 'updated@example.com',
            'address' => 'Updated Address',
            'status' => 'inactive',
            'payment_method' => 'gcash',
        ])->assertSessionHasNoErrors();

        $this->assertDatabaseHas('suppliers', [
            'id' => $supplier->id,
            'name' => 'Updated Supplier',
            'status' => 'inactive',
        ]);
    }

    public function test_user_can_be_updated_without_changing_password(): void
    {
        $user = User::where('role', User::ROLE_INVENTORY)->firstOrFail();

        $this->put(route('users.update', $user), [
            'username' => $user->username,
            'name' => 'Updated Manager',
            'email' => 'updated-manager@example.com',
            'role' => User::ROLE_INVENTORY,
            'status' => 'inactive',
            'password' => '',
        ])->assertSessionHasNoErrors();

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Updated Manager',
            'status' => 'inactive',
        ]);
    }

    public function test_category_can_be_updated(): void
    {
        $category = ProductCategory::where('name', 'Other')->firstOrFail();

        $this->put(route('categories.update', $category), [
            'name' => 'Other Parts',
        ])->assertSessionHasNoErrors();

        $this->assertDatabaseHas('product_categories', [
            'id' => $category->id,
            'name' => 'Other Parts',
        ]);
    }

    public function test_crud_create_and_edit_pages_load(): void
    {
        $variant = ProductVariant::firstOrFail();
        $supplier = Supplier::firstOrFail();
        $category = ProductCategory::firstOrFail();
        $user = User::where('role', User::ROLE_INVENTORY)->firstOrFail();

        $this->get(route('inventory.create'))->assertOk();
        $this->get(route('inventory.edit', $variant))->assertOk();
        $this->get(route('inventory.show', $variant))->assertOk()->assertSee($variant->variant_code);
        $this->get(route('stock-receipts.index'))->assertOk()->assertSee('Stock Receipts');
        $this->get(route('stock-receipts.create'))->assertOk()->assertSee('Receive Stock');
        $this->get(route('categories.index'))->assertOk()->assertSee('variants tracked');
        $this->get(route('categories.index', ['category' => $category->id]))->assertOk()->assertSee('Product / Variants');
        $this->get(route('suppliers.create'))->assertOk();
        $this->get(route('suppliers.edit', $supplier))->assertOk();
        $this->get(route('categories.create'))->assertOk();
        $this->get(route('categories.edit', $category))->assertOk();
        $this->get(route('users.create'))->assertOk();
        $this->get(route('users.edit', $user))->assertOk();
    }

    public function test_inventory_variant_can_store_image_and_render_view_page(): void
    {
        Storage::fake('public');

        $product = Product::firstOrFail();
        $supplier = Supplier::firstOrFail();

        $this->post(route('inventory.store'), [
            'product_id' => $product->id,
            'code' => 'AP-IMG-001',
            'barcode' => '',
            'part_number' => 'IMG-PN-001',
            'brand' => 'Image Test Brand',
            'size' => 'Standard',
            'color' => 'Silver',
            'image' => UploadedFile::fake()->createWithContent(
                'variant.png',
                base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+/p9sAAAAASUVORK5CYII=')
            ),
            'price' => 2200,
            'cost' => 1200,
            'stock' => 6,
            'min_stock' => 2,
            'critical_stock' => 1,
            'unit' => 'PCS',
            'supplier_id' => $supplier->id,
            'status' => 'active',
        ])->assertRedirect(route('inventory.index'))
            ->assertSessionHasNoErrors();

        $variant = ProductVariant::where('variant_code', 'AP-IMG-001')->firstOrFail();

        $this->assertNotNull($variant->image_path);
        Storage::disk('public')->assertExists($variant->image_path);
        $this->assertDatabaseHas('product_variants', [
            'id' => $variant->id,
            'image_path' => $variant->image_path,
        ]);

        $this->get(route('inventory.show', $variant))
            ->assertOk()
            ->assertSee('Read-only product variant details.')
            ->assertSee($variant->variant_code)
            ->assertSee($variant->image_path);
    }

    public function test_stock_overview_movements_and_adjustment_work(): void
    {
        $variant = ProductVariant::with('inventoryStock')->firstOrFail();
        $startingStock = (int) $variant->inventoryStock->quantity_on_hand;

        $this->get(route('inventory.index'))->assertOk()->assertSee('Inventory');
        $this->get(route('inventory.stock-overview'))
            ->assertOk()
            ->assertSee('Stock Overview')
            ->assertSee('id="stockOverviewLiveFilter"', false)
            ->assertSee('data-live-filter', false)
            ->assertSee('data-live-results', false)
            ->assertSee('data-live-filter-choice="stock_level"', false)
            ->assertDontSee('id="inventoryStockLevel"', false);
        $this->get(route('inventory.movements'))->assertOk()->assertSee('Stock Movements');

        $this->post(route('inventory.adjust-stock', $variant), [
            'type' => 'stock_in',
            'quantity' => 5,
            'remarks' => 'Test stock adjustment',
        ])->assertSessionHasNoErrors();

        $this->assertDatabaseHas('inventory_stocks', [
            'variant_id' => $variant->id,
            'quantity_on_hand' => $startingStock + 5,
        ]);

        $this->assertDatabaseHas('inventory_movements', [
            'variant_id' => $variant->id,
            'movement_type' => 'stock_in',
            'quantity_change' => 5,
            'quantity_before' => $startingStock,
            'quantity_after' => $startingStock + 5,
            'remarks' => 'Test stock adjustment',
        ]);
    }

    public function test_stock_overview_filters_stock_alert_levels_correctly(): void
    {
        $variants = ProductVariant::take(4)->get();
        $this->assertGreaterThanOrEqual(4, $variants->count());

        InventoryStock::query()->update([
            'quantity_on_hand' => 20,
            'minimum_stock' => 5,
            'critical_stock' => 2,
        ]);

        [$out, $critical, $low, $normal] = $variants->values();

        InventoryStock::where('variant_id', $out->id)->update(['quantity_on_hand' => 0]);
        InventoryStock::where('variant_id', $critical->id)->update(['quantity_on_hand' => 1]);
        InventoryStock::where('variant_id', $low->id)->update(['quantity_on_hand' => 4]);
        InventoryStock::where('variant_id', $normal->id)->update(['quantity_on_hand' => 20]);

        $this->get(route('inventory.stock-overview', ['stock_level' => 'attention']))
            ->assertOk()
            ->assertSee('Needs Attention (3)')
            ->assertSee($out->variant_code)
            ->assertSee($critical->variant_code)
            ->assertSee($low->variant_code)
            ->assertDontSee($normal->variant_code);

        $this->get(route('inventory.stock-overview', ['stock_level' => 'out']))
            ->assertOk()
            ->assertSee('Out of Stock (1)')
            ->assertSee($out->variant_code)
            ->assertDontSee($critical->variant_code)
            ->assertDontSee($low->variant_code);

        $this->get(route('inventory.stock-overview', ['stock_level' => 'critical']))
            ->assertOk()
            ->assertSee('Critical (1)')
            ->assertSee($critical->variant_code)
            ->assertDontSee($out->variant_code)
            ->assertDontSee($low->variant_code);

        $this->get(route('inventory.stock-overview', ['stock_level' => 'low']))
            ->assertOk()
            ->assertSee('Low Stock (1)')
            ->assertSee($low->variant_code)
            ->assertDontSee($out->variant_code)
            ->assertDontSee($critical->variant_code);
    }

    public function test_large_variant_stock_change_creates_inventory_intrusion_event(): void
    {
        SecurityRule::where('rule_name', 'bulk_inventory_change')->update([
            'threshold_value' => 2,
            'severity' => 'high',
            'action' => 'alert',
            'is_active' => true,
        ]);
        SecurityRule::where('rule_name', 'inventory_discrepancy_spike')->update([
            'threshold_value' => 99,
        ]);

        $variant = ProductVariant::with('product', 'inventoryStock')->whereNotNull('part_number')->firstOrFail();
        $startingStock = (int) $variant->inventoryStock->quantity_on_hand;

        $this->post(route('inventory.adjust-stock', $variant), [
            'type' => 'stock_in',
            'quantity' => 3,
            'remarks' => 'IDS bulk inventory test.',
        ])->assertSessionHasNoErrors();

        $event = IntrusionEvent::where('event_type', 'bulk_inventory_change')->latest('id')->firstOrFail();

        $this->assertSame('inventory', $event->source_module);
        $this->assertStringContainsString('product_name: '.$variant->product->name, $event->description);
        $this->assertStringContainsString('variant_code: '.$variant->variant_code, $event->description);
        $this->assertStringContainsString('part_number: '.$variant->part_number, $event->description);
        $this->assertStringContainsString('quantity_change: 3', $event->description);
        $this->assertStringContainsString('user: admin', $event->description);
        $this->assertStringContainsString('reference_type: manual_adjustment', $event->description);

        $this->assertDatabaseHas('inventory_movements', [
            'variant_id' => $variant->id,
            'movement_type' => 'stock_in',
            'quantity_change' => 3,
            'quantity_before' => $startingStock,
            'quantity_after' => $startingStock + 3,
            'reference_type' => 'manual_adjustment',
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'IDS_INVENTORY',
            'affected_table' => 'inventory_stocks',
            'affected_record_id' => (string) $variant->inventoryStock->id,
        ]);
        $this->assertDatabaseHas('intrusion_alerts', [
            'intrusion_event_id' => $event->id,
            'severity' => 'high',
        ]);
    }

    public function test_inventory_discrepancy_spike_uses_variant_movement_history(): void
    {
        SecurityRule::where('rule_name', 'bulk_inventory_change')->update([
            'threshold_value' => 999,
        ]);
        SecurityRule::where('rule_name', 'inventory_discrepancy_spike')->update([
            'threshold_value' => 2,
            'time_window_minutes' => 60,
            'severity' => 'high',
            'action' => 'alert',
            'is_active' => true,
        ]);

        $variant = ProductVariant::with('product', 'inventoryStock')->whereNotNull('part_number')->firstOrFail();

        $this->post(route('inventory.adjust-stock', $variant), [
            'type' => 'stock_in',
            'quantity' => 1,
            'remarks' => 'IDS spike first movement.',
        ])->assertSessionHasNoErrors();

        $this->post(route('inventory.adjust-stock', $variant), [
            'type' => 'stock_out',
            'quantity' => 1,
            'remarks' => 'IDS spike second movement.',
        ])->assertSessionHasNoErrors();

        $event = IntrusionEvent::where('event_type', 'inventory_discrepancy_spike')->latest('id')->firstOrFail();

        $this->assertSame('inventory', $event->source_module);
        $this->assertStringContainsString('product_name: '.$variant->product->name, $event->description);
        $this->assertStringContainsString('variant_code: '.$variant->variant_code, $event->description);
        $this->assertStringContainsString('part_number: '.$variant->part_number, $event->description);
        $this->assertStringContainsString('quantity_change: -1', $event->description);
        $this->assertStringContainsString('reference_type: manual_adjustment', $event->description);

        $this->assertSame(2, InventoryMovement::where('variant_id', $variant->id)
            ->where('movement_type', '!=', 'sale')
            ->count());
        $this->assertTrue(AuditLog::where('action', 'IDS_INVENTORY')
            ->where('affected_table', 'inventory_stocks')
            ->where('affected_record_id', (string) $variant->inventoryStock->id)
            ->exists());
    }

    public function test_stock_receipt_receives_multiple_variants_and_records_movements(): void
    {
        $supplier = Supplier::firstOrFail();
        $variants = ProductVariant::with('inventoryStock')->take(2)->get();

        $this->assertCount(2, $variants);

        $first = $variants[0];
        $second = $variants[1];
        $firstBefore = (int) $first->inventoryStock->quantity_on_hand;
        $secondBefore = (int) $second->inventoryStock->quantity_on_hand;

        $response = $this->post(route('stock-receipts.store'), [
            'supplier_id' => $supplier->id,
            'date_received' => '2026-06-25',
            'remarks' => 'Feature test receipt.',
            'items' => [
                [
                    'variant_id' => $first->id,
                    'quantity_received' => 3,
                    'unit_cost' => 155.25,
                ],
                [
                    'variant_id' => $second->id,
                    'quantity_received' => 4,
                    'unit_cost' => 255.50,
                ],
            ],
        ]);

        $receipt = StockReceipt::latest('id')->firstOrFail();

        $response->assertRedirect(route('stock-receipts.show', $receipt))
            ->assertSessionHasNoErrors();

        $this->get(route('stock-receipts.show', $receipt))
            ->assertOk()
            ->assertSee($receipt->receipt_no)
            ->assertSee($first->variant_code)
            ->assertSee($second->variant_code);

        $this->assertDatabaseHas('stock_receipts', [
            'id' => $receipt->id,
            'supplier_id' => $supplier->id,
            'received_by' => auth()->id(),
        ]);
        $this->assertSame('2026-06-25', $receipt->date_received->toDateString());

        $this->assertDatabaseHas('stock_receipt_items', [
            'receipt_id' => $receipt->id,
            'variant_id' => $first->id,
            'quantity_received' => 3,
            'unit_cost' => '155.25',
            'total_cost' => '465.75',
        ]);

        $this->assertDatabaseHas('stock_receipt_items', [
            'receipt_id' => $receipt->id,
            'variant_id' => $second->id,
            'quantity_received' => 4,
            'unit_cost' => '255.50',
            'total_cost' => '1022.00',
        ]);

        $this->assertSame($firstBefore + 3, InventoryStock::where('variant_id', $first->id)->value('quantity_on_hand'));
        $this->assertSame($secondBefore + 4, InventoryStock::where('variant_id', $second->id)->value('quantity_on_hand'));
        $this->assertSame('155.25', $first->fresh()->cost_price);
        $this->assertSame('255.50', $second->fresh()->cost_price);

        $this->assertDatabaseHas('inventory_movements', [
            'variant_id' => $first->id,
            'movement_type' => 'stock_in',
            'quantity_change' => 3,
            'quantity_before' => $firstBefore,
            'quantity_after' => $firstBefore + 3,
            'reference_type' => 'stock_receipt',
            'reference_id' => $receipt->id,
        ]);

        $this->assertDatabaseHas('inventory_movements', [
            'variant_id' => $second->id,
            'movement_type' => 'stock_in',
            'quantity_change' => 4,
            'quantity_before' => $secondBefore,
            'quantity_after' => $secondBefore + 4,
            'reference_type' => 'stock_receipt',
            'reference_id' => $receipt->id,
        ]);

        $this->assertSame(2, InventoryMovement::where('reference_type', 'stock_receipt')
            ->where('reference_id', $receipt->id)
            ->count());
    }

    public function test_admin_report_export_logs_audit_entry(): void
    {
        SecurityRule::where('rule_name', 'large_report_date_range')->update(['is_active' => false]);

        $this->get(route('reports.export', ['range' => 'today']))
            ->assertOk()
            ->assertDownload();

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'REPORT_EXPORT',
            'affected_table' => 'reports',
            'user_id' => auth()->id(),
        ]);
    }

    public function test_sales_report_exports_have_company_headers_in_csv_and_pdf(): void
    {
        SecurityRule::whereIn('rule_name', ['bulk_data_export', 'large_report_date_range'])
            ->update(['is_active' => false]);

        $csvResponse = $this->get(route('reports.export', ['range' => 'today']))->assertOk();
        $csv = $csvResponse->streamedContent();

        $this->assertStringContainsString(app(SystemSettingsService::class)->get('business.name'), $csv);
        $this->assertStringContainsString('SALES REPORT', $csv);
        $this->assertStringContainsString('"REPORT PERIOD","'.today()->format('F j, Y').'"', $csv);

        $monthCsv = $this->get(route('reports.export', ['range' => 'month']))->assertOk()->streamedContent();
        $this->assertStringContainsString('"REPORT PERIOD","October 2026"', $monthCsv);

        $customCsv = $this->get(route('reports.export', [
            'range' => 'custom',
            'date_from' => '2026-09-01',
            'date_to' => '2026-09-30',
        ]))->assertOk()->streamedContent();
        $this->assertStringContainsString('"REPORT PERIOD","September 1–30, 2026"', $customCsv);

        $pdfResponse = $this->get(route('reports.export.pdf', ['range' => 'today']))->assertOk();
        $this->assertStringStartsWith('application/pdf', (string) $pdfResponse->headers->get('content-type'));
        $this->assertStringStartsWith('%PDF-', $pdfResponse->getContent());
    }

    public function test_inventory_report_exports_all_filtered_records_to_csv_and_pdf(): void
    {
        SecurityRule::where('rule_name', 'bulk_data_export')->update(['is_active' => false]);
        $variant = ProductVariant::with('product')->firstOrFail();

        $parameters = ['search' => $variant->variant_code];
        $csvResponse = $this->get(route('reports.inventory.export', $parameters))->assertOk();
        $csv = $csvResponse->streamedContent();

        $this->assertStringContainsString(app(SystemSettingsService::class)->get('business.name'), $csv);
        $this->assertStringContainsString('INVENTORY STOCK REPORT', $csv);
        $this->assertStringContainsString($variant->variant_code, $csv);
        $this->assertStringContainsString($variant->product->name, $csv);

        $excelResponse = $this->get(route('reports.inventory.export.xlsx', $parameters))
            ->assertOk()
            ->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $this->assertStringStartsWith('PK', $excelResponse->getContent());

        $pdfResponse = $this->get(route('reports.inventory.export.pdf', $parameters))->assertOk();
        $this->assertStringStartsWith('application/pdf', (string) $pdfResponse->headers->get('content-type'));
        $this->assertStringStartsWith('%PDF-', $pdfResponse->getContent());
    }

    public function test_repeated_report_export_creates_intrusion_event(): void
    {
        SecurityRule::where('rule_name', 'bulk_data_export')->update([
            'threshold_value' => 1,
            'severity' => 'medium',
            'action' => 'alert',
        ]);
        SecurityRule::where('rule_name', 'large_report_date_range')->update(['is_active' => false]);

        $this->get(route('reports.export', ['range' => 'today']))->assertOk();
        $this->get(route('reports.export', ['range' => 'today']))->assertOk();

        $this->assertDatabaseHas('intrusion_events', [
            'event_type' => 'bulk_data_export',
            'source_module' => 'reports',
            'severity' => 'medium',
        ]);
    }

    public function test_cashier_report_export_attempt_is_logged_as_unauthorized_endpoint_access(): void
    {
        SecurityRule::where('rule_name', 'unauthorized_endpoint_access')->update([
            'severity' => 'medium',
            'action' => 'alert',
        ]);

        $cashier = User::where('role', User::ROLE_CASHIER)->firstOrFail();
        $this->actingAs($cashier);

        $this->get(route('reports.export'))->assertForbidden();

        $this->assertDatabaseHas('intrusion_events', [
            'user_id' => $cashier->id,
            'event_type' => 'unauthorized_endpoint_access',
            'request_path' => '/reports/export',
        ]);
    }

    public function test_intrusion_events_create_actionable_dashboard_and_email_alerts(): void
    {
        Mail::fake();
        $request = Request::create('/security-test', 'POST');
        $request->server->set('REMOTE_ADDR', '127.0.0.1');
        $request->headers->set('User-Agent', 'Acry-Link IDS Test');
        $request->setUserResolver(fn () => auth()->user());

        $event = app(IntrusionDetectionService::class)->createIntrusionEvent(
            'sql_injection_attempt',
            'critical',
            'Harmless IDS regression test.',
            $request,
            auth()->user(),
            SecurityRule::where('rule_name', 'sql_injection_attempt')->first(),
            'input_validation'
        );

        $this->assertDatabaseHas('intrusion_alerts', [
            'intrusion_event_id' => $event->id,
            'alert_title' => 'SQL INJECTION ATTEMPT',
            'alert_status' => 'new',
            'alert_channel' => 'both',
            'severity' => 'critical',
        ]);

        $mediumEvent = app(IntrusionDetectionService::class)->createIntrusionEvent(
            'duplicate_session',
            'medium',
            'Harmless medium IDS regression test.',
            $request,
            auth()->user(),
            SecurityRule::where('rule_name', 'duplicate_session')->first(),
            'session'
        );

        $this->assertDatabaseHas('intrusion_alerts', [
            'intrusion_event_id' => $mediumEvent->id,
            'alert_title' => 'DUPLICATE SESSION',
            'alert_status' => 'new',
            'alert_channel' => 'both',
            'severity' => 'medium',
        ]);

        $this->assertDatabaseHas('notification_logs', [
            'recipient_email' => auth()->user()->email,
            'notification_type' => 'intrusion_alert',
            'send_status' => 'sent',
        ]);

        $admin = User::where('role', User::ROLE_ADMIN)->firstOrFail();
        $this->actingAs($admin)
            ->get(route('security.events'))
            ->assertOk()
            ->assertSee('2 alerts')
            ->assertSee('DUPLICATE SESSION')
            ->assertSee('Acknowledge')
            ->assertSee('Resolve');

        $alert = $event->alerts()->firstOrFail();
        $this->get(route('security.events'))
            ->assertOk()
            ->assertSee(route('security.events.alerts.show', $alert), false);

        $this->get(route('security.events.alerts.show', $alert))
            ->assertOk()
            ->assertSee($alert->alert_title)
            ->assertSee('Alert Information');

        $this->get(route('security.events.show', $mediumEvent))
            ->assertOk()
            ->assertSee('IDS Event Details')
            ->assertSee($mediumEvent->description);

        $this->assertFalse(Schema::hasColumn('intrusion_alerts', 'title'));
        $this->assertFalse(Schema::hasColumn('intrusion_alerts', 'message'));
    }

    public function test_authorized_device_view_data_and_filters_are_rendered(): void
    {
        $device = AuthorizedDevice::with('assignedUser')->firstOrFail();

        $this->get(route('security.devices'))
            ->assertOk()
            ->assertSee('type="application/json" id="authorizedDevicesData"', false)
            ->assertSee('data-view-device="'.$device->id.'"', false)
            ->assertSee($device->device_name)
            ->assertSee('Device Details')
            ->assertSee('data-open-device-register', false)
            ->assertSee('id="registerDeviceModal"', false)
            ->assertSee('device-metrics', false)
            ->assertSee('data-device-status-filter="authorized"', false)
            ->assertSee('data-device-status-filter=""', false);

        $this->get(route('security.devices', [
            'search' => $device->device_name,
            'status' => $device->status,
        ]))
            ->assertOk()
            ->assertSee($device->device_name)
            ->assertViewHas('devices', fn ($devices) => $devices->contains('id', $device->id));
    }
}
