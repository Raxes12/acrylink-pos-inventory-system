<?php

namespace Tests\Feature;

use App\Http\Middleware\CheckBlockedEntity;
use App\Http\Middleware\DenyBlockedDevice;
use App\Http\Middleware\DetectInputAttack;
use App\Http\Middleware\DetectSessionAnomaly;
use App\Http\Middleware\TrackUserSession;
use App\Http\Middleware\ValidateAuthorizedDevice;
use App\Models\SystemSetting;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SystemSettingsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        Cache::flush();
        $this->withoutMiddleware([
            CheckBlockedEntity::class, DenyBlockedDevice::class, DetectInputAttack::class,
            DetectSessionAnomaly::class, TrackUserSession::class, ValidateAuthorizedDevice::class,
        ]);
    }

    public function test_admin_can_open_settings_and_save_real_business_and_receipt_values(): void
    {
        $this->actingAs(User::where('role', User::ROLE_ADMIN)->firstOrFail());

        $this->get(route('settings.index'))->assertOk()
            ->assertSee('System Settings')
            ->assertSee('Client Business Information')
            ->assertSee('VMC Auto Parts &amp; Accessories', false)
            ->assertSee('<title>Acry-Link &ndash; System Settings</title>', false);
        $this->get(route('settings.index', ['section' => 'inventory']))->assertOk()->assertSee('Product Categories');
        $this->put(route('settings.general.update'), [
            'business_name' => 'Acry-Link Trading',
            'business_address' => 'Ilagan City',
            'business_contact_number' => '09170000000',
            'business_email' => 'office@example.test',
        ])->assertRedirect();
        $this->put(route('settings.receipt.update'), [
            'footer_message' => 'Thank you for choosing Acry-Link.',
            'show_cashier' => 1,
            'show_payment_method' => 1,
        ])->assertRedirect();

        $this->assertDatabaseHas('system_settings', ['key' => 'business.name', 'value' => 'Acry-Link Trading']);
        $this->assertDatabaseHas('system_settings', ['key' => 'receipt.show_customer', 'value' => '0']);
        $this->get(route('settings.index', ['section' => 'general']))->assertSee('Acry-Link Trading');
    }

    public function test_non_admin_cannot_access_settings_or_security_center(): void
    {
        $this->actingAs(User::where('role', User::ROLE_CASHIER)->firstOrFail());
        $settingsCount = SystemSetting::count();
        $this->get(route('settings.index'))->assertForbidden();
        $this->put(route('settings.general.update'), ['business_name' => 'Unauthorized'])->assertForbidden();
        $this->get(route('security.index'))->assertForbidden();
        $this->assertSame($settingsCount, SystemSetting::count());
    }

    public function test_admin_can_upload_and_replace_the_business_logo(): void
    {
        Storage::fake('public');
        $this->actingAs(User::where('role', User::ROLE_ADMIN)->firstOrFail());

        $this->put(route('settings.general.update'), [
            'business_name' => 'VMC Auto Parts & Accessories',
            'business_logo' => UploadedFile::fake()->createWithContent(
                'vmc-brand.png',
                base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+A8AAQUBAScY42YAAAAASUVORK5CYII=')
            ),
        ])->assertRedirect()->assertSessionHasNoErrors();

        $logoPath = SystemSetting::where('key', 'business.logo_path')->value('value');
        $this->assertStringStartsWith('business-logos/', $logoPath);
        Storage::disk('public')->assertExists($logoPath);
        $this->get(route('settings.index'))->assertOk()->assertSee(Storage::disk('public')->url($logoPath), false);

        $this->put(route('settings.general.update'), [
            'business_name' => 'VMC Auto Parts & Accessories',
            'reset_business_logo' => 1,
        ])->assertRedirect()->assertSessionHasNoErrors();

        Storage::disk('public')->assertMissing($logoPath);
        $this->assertDatabaseHas('system_settings', [
            'key' => 'business.logo_path',
            'value' => 'logo/vmc-logo.ico',
        ]);
    }

    public function test_business_logo_rejects_unsupported_files(): void
    {
        Storage::fake('public');
        $this->actingAs(User::where('role', User::ROLE_ADMIN)->firstOrFail());

        $this->put(route('settings.general.update'), [
            'business_name' => 'VMC Auto Parts & Accessories',
            'business_logo' => UploadedFile::fake()->create('logo.svg', 10, 'image/svg+xml'),
        ])->assertSessionHasErrors('business_logo');

        $this->assertDatabaseMissing('system_settings', ['key' => 'business.logo_path']);
    }

    public function test_admin_can_manage_unused_units_but_cannot_delete_used_units(): void
    {
        $this->actingAs(User::where('role', User::ROLE_ADMIN)->firstOrFail());
        $this->post(route('settings.units.store'), ['code' => 'BOX', 'name' => 'Box'])->assertRedirect();
        $unit = Unit::where('code', 'BOX')->firstOrFail();
        $this->put(route('settings.units.update', $unit), ['code' => 'BX', 'name' => 'Boxes'])->assertRedirect();
        $this->delete(route('settings.units.destroy', $unit))->assertRedirect();
        $this->assertDatabaseMissing('units', ['id' => $unit->id]);

        $used = Unit::whereHas('productVariants')->firstOrFail();
        $this->delete(route('settings.units.destroy', $used))->assertSessionHasErrors('unit');
        $this->assertDatabaseHas('units', ['id' => $used->id]);
    }

    public function test_security_center_uses_database_backed_metrics(): void
    {
        $this->actingAs(User::where('role', User::ROLE_ADMIN)->firstOrFail());
        $this->get(route('security.index'))
            ->assertOk()
            ->assertSee('Security Center')
            ->assertSee('Enabled rules')
            ->assertSee('Active blocks')
            ->assertSee('Authorized devices');
    }
}
