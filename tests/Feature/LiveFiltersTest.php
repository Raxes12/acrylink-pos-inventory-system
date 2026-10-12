<?php

namespace Tests\Feature;

use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LiveFiltersTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        $this->actingAs(User::where('role', User::ROLE_ADMIN)->firstOrFail());
    }

    public function test_supplier_search_finds_a_record_beyond_the_first_paginated_page(): void
    {
        foreach (range(1, 12) as $number) {
            Supplier::create(['name' => sprintf('Alpha Supplier %02d', $number), 'status' => 'active', 'payment_method' => 'cash']);
        }
        Supplier::create(['name' => 'Zebra Remote Match', 'status' => 'active', 'payment_method' => 'cash']);

        $this->get(route('suppliers.index'))
            ->assertOk()
            ->assertDontSee('Zebra Remote Match');

        $this->get(route('suppliers.index', ['search' => 'Zebra Remote Match']))
            ->assertOk()
            ->assertSee('Zebra Remote Match')
            ->assertSee('data-live-results', false);
    }

    public function test_supplier_directory_uses_live_filters_and_contains_the_create_modal(): void
    {
        $this->get(route('suppliers.index'))
            ->assertOk()
            ->assertSee('data-live-filter', false)
            ->assertSee('data-live-filter-status', false)
            ->assertSee('data-open-supplier-create', false)
            ->assertSee('id="createSupplierModal"', false)
            ->assertDontSee('clearSupplierFilters', false)
            ->assertDontSee('>Apply<', false);
    }

    public function test_invalid_supplier_creation_reopens_modal_with_entered_values(): void
    {
        $response = $this->from(route('suppliers.index'))->post(route('suppliers.store'), [
            '_supplier_create' => '1',
            'name' => 'Supplier With Invalid Email',
            'email' => 'invalid-email',
            'status' => 'active',
            'payment_method' => 'cash',
        ]);

        $response->assertRedirect(route('suppliers.index'))->assertSessionHasErrors('email');

        $this->get(route('suppliers.index'))
            ->assertOk()
            ->assertSee('data-open-on-error="true"', false)
            ->assertSee('Supplier With Invalid Email');
    }

    public function test_filter_pages_keep_standard_get_requests_as_a_fallback(): void
    {
        foreach (['security.events', 'security.blocked-entities', 'security.devices', 'pos.transactions', 'inventory.movements', 'reports.inventory'] as $route) {
            $this->get(route($route))
                ->assertOk()
                ->assertSee('data-live-filter', false)
                ->assertSee('data-live-results', false);
        }
    }

    public function test_blocklist_exposes_realtime_search_and_status_controls(): void
    {
        $this->get(route('security.blocked-entities'))
            ->assertOk()
            ->assertSee('id="blockedEntitySearch"', false)
            ->assertSee('data-live-filter', false)
            ->assertSee('data-live-filter-status', false)
            ->assertDontSee('>Search</button>', false)
            ->assertDontSee('>Clear</a>', false)
            ->assertSee('Blocked entities');
    }

    public function test_intrusion_alerts_expose_buttonless_realtime_filters(): void
    {
        $this->get(route('security.events'))
            ->assertOk()
            ->assertSee('id="securityEventSearch"', false)
            ->assertSee('data-live-filter', false)
            ->assertSee('data-live-filter-status', false)
            ->assertSee('name="severity"', false)
            ->assertSee('name="status"', false)
            ->assertSee('Anomaly Alerts')
            ->assertDontSee('Manage Intrusion Detections')
            ->assertDontSee('>Apply</button>', false)
            ->assertDontSee('>Clear</a>', false);
    }

    public function test_receivables_report_uses_live_primary_filters_and_manual_date_filters(): void
    {
        $this->get(route('reports.accounts-receivable'))
            ->assertOk()
            ->assertSee('data-live-filter', false)
            ->assertDontSee('data-live-submit-only', false)
            ->assertSee('name="customer"', false)
            ->assertSee('name="payment_status"', false)
            ->assertSee('name="due_from"', false)
            ->assertSee('data-live-manual', false)
            ->assertSee('Apply Dates')
            ->assertSee('Clear Dates')
            ->assertDontSee('Apply Filters')
            ->assertDontSee('Clear All');
    }

    public function test_inventory_report_uses_buttonless_realtime_filters(): void
    {
        $this->get(route('reports.inventory'))
            ->assertOk()
            ->assertSee('data-live-filter', false)
            ->assertDontSee('data-live-submit-only', false)
            ->assertSee('data-live-sync="report-actions"', false)
            ->assertSee('data-live-results', false)
            ->assertDontSee('>Apply</button>', false)
            ->assertDontSee('>Clear</a>', false);
    }
}
