<?php

namespace Tests\Feature;

use App\Http\Middleware\CheckBlockedEntity;
use App\Http\Middleware\DenyBlockedDevice;
use App\Http\Middleware\DetectSessionAnomaly;
use App\Http\Middleware\TrackUserSession;
use App\Http\Middleware\ValidateAuthorizedDevice;
use App\Models\AuditLog;
use App\Models\Customer;
use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use Tests\TestCase;

class CustomerManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        // Keep authentication, roles, input detection and rate checks enabled.
        $this->withoutMiddleware([
            CheckBlockedEntity::class, DenyBlockedDevice::class,
            DetectSessionAnomaly::class, TrackUserSession::class, ValidateAuthorizedDevice::class,
        ]);
    }

    public function test_admin_can_create_view_and_edit_customer_with_audit_context(): void
    {
        $admin = User::where('role', User::ROLE_ADMIN)->firstOrFail();
        $this->actingAs($admin);
        $this->get(route('customers.index'))
            ->assertOk()
            ->assertSee('data-open-customer-create', false)
            ->assertSee('id="createCustomerModal"', false);
        $this->get(route('customers.create'))->assertOk()->assertSee('Create Customer');
        $this->post(route('customers.store'), $this->payload())->assertSessionHasNoErrors()->assertRedirect();
        $customer = Customer::firstOrFail();
        $this->assertSame('CUST-000001', $customer->customer_code);
        $this->get(route('customers.show', $customer))->assertOk()->assertSee('Alice Customer')->assertSee('CUST-000001');
        $this->get(route('customers.edit', $customer))->assertOk()->assertSee('Save Changes');
        $log = $this->event('CUSTOMER_CREATED', $customer);
        $this->assertSame($admin->id, $log->user_id);
        $this->assertSame('Alice Customer', $log->new_values['customer_name']);

        $this->put(route('customers.update', $customer), $this->payload(['phone' => '09999999999']))->assertSessionHasNoErrors()->assertRedirect(route('customers.show', $customer));
        $this->assertSame('09999999999', $customer->fresh()->phone);
        $log = $this->event('CUSTOMER_UPDATED', $customer);
        $this->assertSame(['phone' => '09123456789'], $log->old_values);
        $this->assertSame(['phone' => '09999999999'], $log->new_values);
    }

    public function test_credit_changes_log_exact_old_and_new_values_and_no_ops_do_not_log(): void
    {
        $this->admin();
        $customer = Customer::createWithGeneratedCode($this->payload());
        $data = $this->payload(['credit_enabled' => '1', 'credit_limit' => '12500.50', 'default_credit_term_days' => '60']);
        $this->put(route('customers.update', $customer), $data)->assertSessionHasNoErrors();

        foreach ([
            ['CREDIT_ENABLED', 'credit_enabled', false, true],
            ['CREDIT_LIMIT_CHANGED', 'credit_limit', '0.00', '12500.50'],
            ['CREDIT_TERM_CHANGED', 'default_credit_term_days', 30, 60],
        ] as [$action, $field, $before, $after]) {
            $log = $this->event($action, $customer);
            $this->assertSame([$field => $before], $log->old_values);
            $this->assertSame([$field => $after], $log->new_values);
        }
        $count = AuditLog::where('affected_table', 'customers')->count();
        $this->put(route('customers.update', $customer), $data)->assertSessionHasNoErrors();
        $this->assertSame($count, AuditLog::where('affected_table', 'customers')->count());

        $this->put(route('customers.update', $customer), array_replace($data, ['credit_enabled' => '0']))->assertSessionHasNoErrors();
        $this->assertSame(['credit_enabled' => false], $this->event('CREDIT_DISABLED', $customer)->new_values);
        $this->assertSame('12500.50', $customer->fresh()->credit_limit);
        $this->getJson(route('audit.results', ['module' => 'customers', 'action' => 'CREDIT_LIMIT_CHANGED']))->assertOk()->assertJsonPath('html', fn ($html) => str_contains($html, $customer->customer_code));
    }

    public function test_status_changes_are_audited_from_form_and_dedicated_action(): void
    {
        $this->admin();
        $customer = Customer::createWithGeneratedCode($this->payload());
        $this->put(route('customers.update', $customer), $this->payload(['status' => 'inactive']))->assertSessionHasNoErrors();
        $this->assertSame(['status' => 'active'], $this->event('CUSTOMER_DEACTIVATED', $customer)->old_values);
        $this->assertSame('inactive', $customer->fresh()->status);
        $this->patch(route('customers.status', $customer), ['status' => 'active', 'credit_enabled' => true, 'credit_limit' => 500])->assertSessionHasNoErrors();
        $this->assertSame(['status' => 'active'], $this->event('CUSTOMER_ACTIVATED', $customer)->new_values);
        $this->assertFalse($customer->fresh()->credit_enabled);
        $count = AuditLog::where('affected_table', 'customers')->count();
        $this->patch(route('customers.status', $customer), ['status' => 'active'])->assertSessionHasNoErrors();
        $this->assertSame($count, AuditLog::where('affected_table', 'customers')->count());
    }

    public function test_cashiers_and_inventory_staff_cannot_access_or_mutate_customers(): void
    {
        $customer = Customer::createWithGeneratedCode($this->payload());
        foreach ([User::ROLE_CASHIER, User::ROLE_INVENTORY] as $role) {
            $this->actingAs(User::where('role', $role)->firstOrFail());
            foreach (['customers.index', 'customers.create', 'customers.show', 'customers.edit'] as $route) {
                $this->get(route($route, str_ends_with($route, 'show') || str_ends_with($route, 'edit') ? $customer : []))->assertForbidden();
            }
            $this->post(route('customers.store'), $this->payload(['credit_enabled' => true]))->assertForbidden();
            $this->put(route('customers.update', $customer), $this->payload(['credit_enabled' => true, 'credit_limit' => 10000, 'default_credit_term_days' => 90]))->assertForbidden();
            $this->patch(route('customers.update', $customer), ['credit_limit' => 99999])->assertForbidden();
            $this->patch(route('customers.status', $customer), ['status' => 'inactive'])->assertForbidden();
        }
        $this->assertSame(1, Customer::count());
        $this->assertFalse($customer->fresh()->credit_enabled);
        $this->assertSame('0.00', $customer->fresh()->credit_limit);
        $this->assertSame(30, $customer->fresh()->default_credit_term_days);
        $this->assertSame('active', $customer->fresh()->status);
        $this->assertSame(0, AuditLog::where('affected_table', 'customers')->count());
        $this->assertDatabaseHas('intrusion_events', ['event_type' => 'unauthorized_endpoint_access']);
    }

    public function test_guests_are_redirected_and_customer_routes_keep_security_middleware(): void
    {
        $this->get(route('customers.index'))->assertRedirect(route('login'));
        $this->post(route('customers.store'), $this->payload())->assertRedirect(route('login'));
        foreach (['customers.index', 'customers.create', 'customers.show', 'customers.edit', 'customers.store', 'customers.update', 'customers.status'] as $name) {
            $middleware = app('router')->getRoutes()->getByName($name)->gatherMiddleware();
            foreach (['web', 'auth', 'device', 'session.anomaly', 'role:admin'] as $expected) {
                $this->assertContains($expected, $middleware);
            }
        }
    }

    public function test_search_contact_fields_and_combined_filters_include_disabled_credit(): void
    {
        $this->admin();
        $target = Customer::createWithGeneratedCode($this->payload());
        Customer::createWithGeneratedCode($this->payload(['customer_name' => 'Other Customer', 'status' => 'inactive', 'credit_enabled' => true]));
        foreach (['Alice', $target->customer_code, '09123456789', 'alice@example.test', 'Sample Road'] as $search) {
            $this->get(route('customers.index', ['search' => $search, 'status' => 'active', 'credit_enabled' => '0']))
                ->assertOk()->assertViewHas('customers', fn ($rows) => $rows->total() === 1 && $rows->first()->id === $target->id);
        }
        $this->get(route('customers.index', ['status' => 'inactive', 'credit_enabled' => '1']))->assertOk()->assertSee('Other Customer')->assertDontSee('Alice Customer');
        $this->get(route('customers.index', ['search' => 'NoSuchCustomer']))->assertOk()->assertSee('No customers found');
    }

    public function test_listing_is_paginated_and_does_not_query_relationships_per_customer(): void
    {
        $this->admin();
        for ($i = 0; $i < 18; $i++) {
            Customer::createWithGeneratedCode($this->payload(['customer_name' => sprintf('Customer %02d', $i)]));
        }
        DB::enableQueryLog();
        DB::flushQueryLog();
        $this->get(route('customers.index', ['search' => 'Customer', 'credit_enabled' => '0']))->assertOk()
            ->assertViewHas('customers', fn ($rows) => $rows->count() === 15 && $rows->total() === 18)
            ->assertSee('credit_enabled=0', false);
        $customerQueries = collect(DB::getQueryLog())->filter(fn ($query) => str_contains($query['query'], '"customers"'));
        DB::disableQueryLog();
        $this->assertCount(2, $customerQueries); // One count and one page query.
    }

    public function test_validation_rejects_invalid_values_and_customer_code_changes(): void
    {
        $this->admin();
        $invalid = $this->payload([
            'customer_name' => str_repeat('x', 151), 'phone' => str_repeat('1', 31),
            'email' => 'invalid', 'address' => str_repeat('a', 2001), 'status' => 'deleted',
            'credit_enabled' => 'yes', 'credit_limit' => '10000000000.00', 'default_credit_term_days' => 65536,
        ]);
        $this->post(route('customers.store'), $invalid)->assertSessionHasErrors(array_keys($invalid));
        foreach (['-1', '1.234'] as $amount) {
            $this->post(route('customers.store'), $this->payload(['credit_limit' => $amount, 'default_credit_term_days' => 0]))->assertSessionHasErrors(['credit_limit', 'default_credit_term_days']);
        }
        $this->assertSame(0, Customer::count());
        $customer = Customer::createWithGeneratedCode($this->payload());
        $this->put(route('customers.update', $customer), $this->payload(['customer_code' => 'CUST-999999']))->assertSessionHasErrors('customer_code');
        $this->assertSame('CUST-000001', $customer->fresh()->customer_code);
        $this->patch(route('customers.status', $customer), ['status' => 'deleted'])->assertSessionHasErrors('status');
        $this->getJson(route('customers.index', ['status' => 'deleted', 'credit_enabled' => 'yes', 'page' => 0]))->assertUnprocessable()->assertJsonValidationErrors(['status', 'credit_enabled', 'page']);
    }

    public function test_create_validation_returns_to_customer_modal_with_entered_values(): void
    {
        $this->admin();

        $response = $this->from(route('customers.index'))->post(route('customers.store'), $this->payload([
            'customer_name' => '',
            'email' => 'not-an-email',
        ]));

        $response->assertRedirect(route('customers.index'))->assertSessionHasErrors(['customer_name', 'email']);
        $this->get(route('customers.index'))
            ->assertOk()
            ->assertSee('data-open-on-error="true"', false)
            ->assertSee('value="not-an-email"', false);
    }

    public function test_customer_save_and_code_reservation_roll_back_if_audit_fails(): void
    {
        $this->admin();
        $customer = Customer::createWithGeneratedCode($this->payload());
        $this->mock(AuditLogService::class, fn ($mock) => $mock->shouldReceive('record')->andThrow(new RuntimeException('Audit unavailable')));
        $this->withoutExceptionHandling();
        foreach (['create', 'update'] as $operation) {
            try {
                if ($operation === 'create') {
                    $this->post(route('customers.store'), $this->payload());
                } else {
                    $this->put(route('customers.update', $customer), $this->payload(['credit_limit' => 500]));
                }
                $this->fail('Expected the audit failure.');
            } catch (RuntimeException $exception) {
                $this->assertSame('Audit unavailable', $exception->getMessage());
            }
        }
        $this->assertSame(1, Customer::count());
        $this->assertSame('0.00', $customer->fresh()->credit_limit);
        $this->assertDatabaseHas('document_sequences', ['sequence_key' => 'customers', 'current_value' => 1]);
    }

    private function admin(): void
    {
        $this->actingAs(User::where('role', User::ROLE_ADMIN)->firstOrFail());
    }

    private function payload(array $overrides = []): array
    {
        return array_replace([
            'customer_name' => 'Alice Customer', 'phone' => '09123456789', 'email' => 'alice@example.test',
            'address' => '123 Sample Road', 'status' => 'active', 'credit_enabled' => '0',
            'credit_limit' => '0.00', 'default_credit_term_days' => 30,
        ], $overrides);
    }

    private function event(string $action, Customer $customer): AuditLog
    {
        return AuditLog::where('action', $action)->where('affected_table', 'customers')
            ->where('affected_record_id', $customer->id)->latest('id')->firstOrFail();
    }
}

