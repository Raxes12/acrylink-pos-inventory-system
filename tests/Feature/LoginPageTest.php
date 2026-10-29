<?php

namespace Tests\Feature;

use App\Http\Middleware\CheckBlockedEntity;
use App\Http\Middleware\DenyBlockedDevice;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoginPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_page_loads(): void
    {
        $this->withoutMiddleware([
            CheckBlockedEntity::class,
            DenyBlockedDevice::class,
        ]);

        $this->get('/login')
            ->assertOk()
            ->assertHeader('X-Content-Type-Options', 'nosniff')
            ->assertHeader('X-Frame-Options', 'SAMEORIGIN')
            ->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin')
            ->assertHeader('Permissions-Policy', 'camera=(), geolocation=(), microphone=()');
    }

    public function test_cashier_login_ignores_an_admin_only_intended_url(): void
    {
        $this->seed();
        config(['app.device_whitelist_mode' => false]);
        $cashier = User::where('username', 'cashier1')->firstOrFail();

        $this->withSession(['url.intended' => route('dashboard')])
            ->post(route('login.post'), [
                'username' => $cashier->username,
                'password' => 'james123',
            ])
            ->assertRedirect(route('pos.index'));

        $this->assertAuthenticatedAs($cashier);
        $this->assertNull(session('url.intended'));
    }

    public function test_authenticated_cashier_visiting_login_is_redirected_to_pos(): void
    {
        $this->seed();
        $cashier = User::where('role', User::ROLE_CASHIER)->firstOrFail();

        $this->actingAs($cashier)
            ->get(route('login'))
            ->assertRedirect(route('pos.index'));
    }
}
