<?php

namespace Tests\Feature;

use App\Http\Middleware\CheckBlockedEntity;
use App\Http\Middleware\DenyBlockedDevice;
use App\Http\Middleware\DetectInputAttack;
use App\Http\Middleware\DetectSessionAnomaly;
use App\Http\Middleware\TrackUserSession;
use App\Http\Middleware\ValidateAuthorizedDevice;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class ModuleAccessTest extends TestCase
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
    }

    public function test_cashier_can_access_sales_routes(): void
    {
        $cashier = User::where('role', User::ROLE_CASHIER)->firstOrFail();

        $this->actingAs($cashier);

        $this->get(route('pos.index'))->assertOk();
        $this->get(route('pos.transactions'))->assertOk();
    }

    public function test_cashier_cannot_access_inventory_routes(): void
    {
        $cashier = User::where('role', User::ROLE_CASHIER)->firstOrFail();

        $this->actingAs($cashier);

        $this->from(route('pos.index'))
            ->get(route('inventory.index'))
            ->assertForbidden()
            ->assertSee('Go Back')
            ->assertSee('href="'.route('pos.index').'"', false)
            ->assertDontSee('Return to Login');
        $this->assertAuthenticatedAs($cashier);
        $this->get(route('inventory.stock-overview'))->assertForbidden();
        $this->get(route('dashboard'))->assertForbidden();
    }

    public function test_inventory_staff_can_access_inventory_routes(): void
    {
        $staff = User::where('role', User::ROLE_INVENTORY)->firstOrFail();

        $this->actingAs($staff);

        $this->get(route('inventory.index'))->assertOk();
        $this->get(route('inventory.stock-overview'))->assertOk();
        $this->get(route('stock-receipts.index'))->assertOk();
        $this->get(route('suppliers.index'))->assertOk();
    }

    public function test_inventory_staff_cannot_access_sales_routes(): void
    {
        $staff = User::where('role', User::ROLE_INVENTORY)->firstOrFail();

        $this->actingAs($staff);

        $this->get(route('pos.index'))->assertForbidden();
        $this->get(route('pos.transactions'))->assertForbidden();
        $this->get(route('dashboard'))->assertForbidden();
        $this->get(route('reports.index'))->assertForbidden();
        $this->get(route('audit.index'))->assertForbidden();
    }

    public function test_role_default_landing_routes(): void
    {
        $cashier = User::where('role', User::ROLE_CASHIER)->firstOrFail();
        $staff = User::where('role', User::ROLE_INVENTORY)->firstOrFail();
        $admin = User::where('role', User::ROLE_ADMIN)->firstOrFail();

        $this->assertSame(route('pos.index'), $cashier->defaultLandingRoute());
        $this->assertSame(route('inventory.stock-overview'), $staff->defaultLandingRoute());
        $this->assertSame(route('dashboard'), $admin->defaultLandingRoute());
    }

    public function test_device_access_error_requires_login_recovery(): void
    {
        $admin = User::where('role', User::ROLE_ADMIN)->firstOrFail();
        $this->actingAs($admin);

        $this->view('errors.403', [
            'exception' => new HttpException(403, 'This device is not authorized to access this user account.'),
        ])->assertSee('Return to Login')
            ->assertDontSee('>Go Back<', false);
    }
}
