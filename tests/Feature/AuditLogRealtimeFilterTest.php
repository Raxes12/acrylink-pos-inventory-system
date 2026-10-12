<?php

namespace Tests\Feature;

use App\Http\Middleware\CheckBlockedEntity;
use App\Http\Middleware\DenyBlockedDevice;
use App\Http\Middleware\DetectInputAttack;
use App\Http\Middleware\DetectSessionAnomaly;
use App\Http\Middleware\TrackUserSession;
use App\Http\Middleware\ValidateAuthorizedDevice;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuditLogRealtimeFilterTest extends TestCase
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

    public function test_admin_can_filter_audit_results_through_json_endpoint(): void
    {
        $admin = User::where('role', User::ROLE_ADMIN)->firstOrFail();
        AuditLog::create([
            'user_id' => $admin->id,
            'username' => $admin->username,
            'role' => User::ROLE_ADMIN,
            'action' => 'PRODUCT',
            'detail' => 'Realtime audit search marker',
            'ip_address' => '127.0.0.1',
            'timestamp' => now(),
        ]);

        $response = $this->actingAs($admin)->getJson(route('audit.results', [
            'search' => 'Realtime audit search marker',
            'action' => 'PRODUCT',
        ]));

        $response->assertOk()
            ->assertJsonStructure(['html'])
            ->assertJsonPath('html', fn (string $html) => str_contains($html, 'Realtime audit search marker'));
    }

    public function test_audit_results_endpoint_rejects_invalid_filters(): void
    {
        $admin = User::where('role', User::ROLE_ADMIN)->firstOrFail();

        $this->actingAs($admin)
            ->getJson(route('audit.results', ['date_from' => 'not-a-date']))
            ->assertUnprocessable()
            ->assertJsonValidationErrors('date_from');
    }

    public function test_search_covers_network_and_record_context_and_module_filter_is_specific(): void
    {
        $admin = User::where('role', User::ROLE_ADMIN)->firstOrFail();
        AuditLog::create([
            'user_id' => $admin->id,
            'username' => $admin->username,
            'role' => User::ROLE_ADMIN,
            'action' => 'PRODUCT',
            'detail' => 'Updated a product variant',
            'ip_address' => '10.20.30.40',
            'mac_address' => 'AA:BB:CC:DD:EE:FF',
            'user_agent' => 'Audit Search Browser',
            'affected_table' => 'product_variants',
            'affected_record_id' => 'PRDV-0099',
            'timestamp' => now(),
        ]);

        foreach (['AA:BB:CC:DD:EE:FF', 'Audit Search Browser', 'PRDV-0099'] as $search) {
            $this->actingAs($admin)
                ->getJson(route('audit.results', ['search' => $search, 'module' => 'product_variants']))
                ->assertOk()
                ->assertJsonPath('html', fn (string $html) => str_contains($html, 'Updated a product variant'));
        }
    }
}
