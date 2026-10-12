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
use Tests\TestCase;

class UserManagementProtectionTest extends TestCase
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

    public function test_signed_in_admin_can_view_but_cannot_edit_own_account(): void
    {
        $admin = User::where('role', User::ROLE_ADMIN)->firstOrFail();
        $this->actingAs($admin);

        $this->get(route('users.index'))
            ->assertOk()
            ->assertSee(route('users.show', $admin), false)
            ->assertSee('Editing your signed-in administrator account is disabled');

        $this->get(route('users.show', $admin))
            ->assertOk()
            ->assertSee($admin->name)
            ->assertSee('Signed-in administrator protected');

        $this->get(route('users.edit', $admin))->assertForbidden();
        $this->put(route('users.update', $admin), [])->assertForbidden();
    }

    public function test_admin_can_still_view_and_edit_another_staff_account(): void
    {
        $admin = User::where('role', User::ROLE_ADMIN)->firstOrFail();
        $staff = User::where('role', '!=', User::ROLE_ADMIN)->firstOrFail();
        $this->actingAs($admin);

        $this->get(route('users.show', $staff))
            ->assertOk()
            ->assertSee($staff->name)
            ->assertSee(route('users.edit', $staff), false);

        $this->get(route('users.edit', $staff))->assertOk();
    }

    public function test_user_directory_contains_account_summary_and_create_modal(): void
    {
        $admin = User::where('role', User::ROLE_ADMIN)->firstOrFail();

        $this->actingAs($admin)->get(route('users.index'))
            ->assertOk()
            ->assertSee('MFA Protected')
            ->assertSee('data-open-user-create', false)
            ->assertSee('id="createUserModal"', false)
            ->assertSee('name="role_id"', false)
            ->assertSee('name="password_confirmation"', false);
    }

    public function test_create_validation_returns_to_user_modal_with_safe_entered_values(): void
    {
        $admin = User::where('role', User::ROLE_ADMIN)->firstOrFail();
        $this->actingAs($admin);

        $this->from(route('users.index'))->post(route('users.store'), [
            '_user_create' => '1',
            'name' => 'New Staff Member',
            'username' => 'new_staff_member',
            'email' => 'invalid-email',
            'password' => 'secret1',
            'password_confirmation' => 'secret1',
            'role_id' => $admin->role_id,
            'status' => 'active',
        ])->assertRedirect(route('users.index'))->assertSessionHasErrors('email');

        $this->get(route('users.index'))
            ->assertOk()
            ->assertSee('data-open-on-error="true"', false)
            ->assertSee('New Staff Member')
            ->assertDontSee('value="secret1"', false);
    }
}
