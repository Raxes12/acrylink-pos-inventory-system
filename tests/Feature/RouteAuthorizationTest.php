<?php

namespace Tests\Feature;

use App\Http\Middleware\ValidateAuthorizedDevice;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class RouteAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        $this->withoutMiddleware(ValidateAuthorizedDevice::class);
    }

    public function test_all_business_routes_have_authentication_and_expected_role_guards(): void
    {
        $roles = [
            'DashboardController' => 'role:admin',
            'POSController' => 'role:admin,cashier',
            'CreditPaymentController' => 'role:admin,cashier',
            'AccountsReceivableController' => 'role:admin,cashier',
            'InventoryController' => 'role:admin,inventory_staff',
            'StockReceiptController' => 'role:admin,inventory_staff',
            'SupplierController' => 'role:admin,inventory_staff',
            'ReportsController' => 'role:admin',
            'AccountsReceivableReportController' => 'role:admin',
            'AuditLogController' => 'role:admin',
            'SecurityController' => 'role:admin',
            'CategoryController' => 'role:admin',
            'UserController' => 'role:admin',
            'CustomerController' => 'role:admin',
            'SettingsController' => 'role:admin',
            'UnitController' => 'role:admin',
        ];
        $names = [];
        foreach (Route::getRoutes() as $route) {
            $action = $route->getActionName();
            if (! str_starts_with($action, 'App\\Http\\Controllers\\')) {
                continue;
            }
            [$controller, $method] = explode('@', $action);
            $this->assertTrue(method_exists($controller, $method), $action);
            if ($route->getName()) {
                $this->assertNotContains($route->getName(), $names, 'Duplicate route name');
                $names[] = $route->getName();
            }
            if (class_basename($controller) === 'AuthController') {
                continue;
            }
            $middleware = $route->gatherMiddleware();
            $this->assertContains('auth', $middleware, $action);
            $this->assertContains('device', $middleware, $action);
            $this->assertContains('session.anomaly', $middleware, $action);
            $this->assertArrayHasKey(class_basename($controller), $roles);
            $this->assertContains($roles[class_basename($controller)], $middleware, $action);
        }
    }

    public function test_cashier_cannot_access_management_pages_or_submit_management_actions(): void
    {
        $this->actingAs(User::where('role', User::ROLE_CASHIER)->firstOrFail());
        foreach (['dashboard', 'inventory.index', 'stock-receipts.index', 'suppliers.index',
            'reports.index', 'reports.export', 'reports.export.xlsx', 'reports.export.pdf',
            'reports.inventory', 'reports.inventory.export', 'reports.inventory.export.xlsx', 'reports.inventory.export.pdf',
            'reports.accounts-receivable', 'reports.accounts-receivable.export',
            'reports.accounts-receivable.export.xlsx', 'reports.accounts-receivable.export.pdf',
            'audit.index', 'audit.results',
            'security.index', 'security.devices', 'security.events', 'security.rules', 'settings.index', 'users.index', 'categories.index',
            'customers.index'] as $name) {
            $this->get(route($name))->assertForbidden();
        }
        foreach (['inventory.store', 'stock-receipts.store', 'suppliers.store',
            'security.devices.store', 'settings.units.store', 'users.store', 'categories.store', 'customers.store'] as $name) {
            $this->post(route($name), [])->assertForbidden();
        }
    }

    public function test_inventory_staff_cannot_access_sales_or_administration(): void
    {
        $this->actingAs(User::where('role', User::ROLE_INVENTORY)->firstOrFail());
        foreach (['pos.index', 'pos.products', 'pos.transactions', 'accounts-receivable.index',
            'reports.index', 'reports.accounts-receivable', 'users.index', 'security.index', 'security.events', 'settings.index'] as $name) {
            $this->get(route($name))->assertForbidden();
        }
        $this->post(route('pos.checkout'), [])->assertForbidden();
    }

    public function test_inactive_authenticated_accounts_are_logged_out(): void
    {
        foreach ([User::ROLE_ADMIN => 'users.index', User::ROLE_CASHIER => 'pos.transactions',
            User::ROLE_INVENTORY => 'inventory.index'] as $role => $route) {
            $user = User::where('role', $role)->firstOrFail();
            $user->update(['status' => 'inactive']);
            $this->actingAs($user)->get(route($route))->assertForbidden();
            $this->assertGuest();
        }
    }

    public function test_current_role_cannot_be_overridden_by_stale_legacy_admin_role(): void
    {
        $user = User::where('role', User::ROLE_CASHIER)->firstOrFail();
        $user->update([
            'role' => User::ROLE_ADMIN,
            'role_id' => Role::where('role_name', Role::CASHIER)->value('role_id'),
        ]);
        $user->refresh();
        $this->assertFalse($user->isAdmin());
        $this->assertTrue($user->isCashier());
        $this->actingAs($user)->get(route('users.index'))->assertForbidden();
    }

    public function test_guests_are_redirected_from_business_routes(): void
    {
        foreach (['pos.transactions', 'inventory.index', 'reports.index', 'users.index', 'security.events'] as $name) {
            $this->get(route($name))->assertRedirect(route('login'));
        }
    }
}
