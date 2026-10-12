<?php

namespace Tests\Feature;

use App\Http\Middleware\CheckBlockedEntity;
use App\Http\Middleware\DenyBlockedDevice;
use App\Models\MfaChallenge;
use App\Models\AuthorizedDevice;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class AdminOtpLoginPerformanceTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_login_creates_challenge_and_defers_otp_delivery(): void
    {
        Mail::shouldReceive('raw')->once();
        $this->withoutMiddleware([
            CheckBlockedEntity::class,
            DenyBlockedDevice::class,
        ]);

        $user = User::create([
            'username' => 'otp-admin',
            'name' => 'OTP Admin',
            'email' => 'otp-admin@example.test',
            'password' => Hash::make('secret-password'),
            'role' => User::ROLE_ADMIN,
            'status' => 'active',
            'mfa_enabled' => true,
        ]);

        $response = $this->post(route('login.post'), [
            'username' => $user->username,
            'password' => 'secret-password',
        ]);

        $response->assertRedirect(route('login.otp'));
        $this->assertGuest();
        $challenge = MfaChallenge::where('user_id', $user->id)->firstOrFail();
        $this->assertSame(MfaChallenge::STATUS_PENDING, $challenge->status);
        $this->assertStringStartsWith('hmac-sha256:', $challenge->otp_hash);
    }

    public function test_wrong_otp_does_not_reset_the_challenge_expiration(): void
    {
        $this->withoutMiddleware([
            CheckBlockedEntity::class,
            DenyBlockedDevice::class,
        ]);

        $user = User::create([
            'username' => 'expiry-admin',
            'name' => 'Expiry Admin',
            'email' => 'expiry-admin@example.test',
            'password' => Hash::make('secret-password'),
            'role' => User::ROLE_ADMIN,
            'status' => 'active',
            'mfa_enabled' => true,
        ]);
        $expiresAt = now()->addMinutes(3)->startOfSecond();
        $challenge = MfaChallenge::create([
            'user_id' => $user->id,
            'otp_hash' => Hash::make('123456'),
            'delivery_method' => MfaChallenge::METHOD_EMAIL,
            'expires_at' => $expiresAt,
            'attempt_count' => 0,
            'status' => MfaChallenge::STATUS_PENDING,
        ]);
        $otpSession = [
            'challenge_id' => $challenge->getKey(),
            'user_id' => $user->id,
            'remember' => false,
            'expires_at' => $expiresAt->toIso8601String(),
        ];

        $this->withSession(['auth.otp' => $otpSession])
            ->post(route('login.otp.verify'), ['otp' => '654321'])
            ->assertSessionHasErrors('otp');

        $challenge->refresh();
        $this->assertSame(1, $challenge->attempt_count);
        $this->assertTrue($challenge->expires_at->equalTo($expiresAt));

        $this->withSession(['auth.otp' => $otpSession])
            ->get(route('login.otp'))
            ->assertOk()
            ->assertSee('data-expires-at="'.$expiresAt->toIso8601String().'"', false);
    }

    public function test_keyed_otp_hash_can_complete_login(): void
    {
        $this->withoutMiddleware([
            CheckBlockedEntity::class,
            DenyBlockedDevice::class,
        ]);

        $user = User::create([
            'username' => 'hmac-admin',
            'name' => 'HMAC Admin',
            'email' => 'hmac-admin@example.test',
            'password' => Hash::make('secret-password'),
            'role' => User::ROLE_ADMIN,
            'status' => 'active',
            'mfa_enabled' => true,
        ]);
        AuthorizedDevice::create([
            'device_name' => 'OTP test terminal',
            'ip_address' => '127.0.0.1',
            'assigned_user_id' => $user->id,
            'status' => 'authorized',
        ]);
        $otp = '123456';
        $expiresAt = now()->addMinutes(5);
        $challenge = MfaChallenge::create([
            'user_id' => $user->id,
            'otp_hash' => 'hmac-sha256:'.hash_hmac('sha256', $otp, (string) config('app.key')),
            'delivery_method' => MfaChallenge::METHOD_EMAIL,
            'expires_at' => $expiresAt,
            'attempt_count' => 0,
            'status' => MfaChallenge::STATUS_PENDING,
        ]);

        $this->withSession(['auth.otp' => [
            'challenge_id' => $challenge->getKey(),
            'user_id' => $user->id,
            'remember' => false,
            'expires_at' => $expiresAt->toIso8601String(),
        ]])->post(route('login.otp.verify'), ['otp' => $otp])
            ->assertRedirect($user->defaultLandingRoute());

        $this->assertAuthenticatedAs($user);
        $this->assertSame(MfaChallenge::STATUS_VERIFIED, $challenge->refresh()->status);
    }

    public function test_expired_otp_can_be_replaced_without_returning_to_login(): void
    {
        Mail::shouldReceive('raw')->once();

        $user = User::create([
            'username' => 'resend-admin',
            'name' => 'Resend Admin',
            'email' => 'resend-admin@example.test',
            'password' => Hash::make('secret-password'),
            'role' => User::ROLE_ADMIN,
            'status' => 'active',
            'mfa_enabled' => true,
        ]);
        $expiredAt = now()->subMinute();
        $expiredChallenge = MfaChallenge::create([
            'user_id' => $user->id,
            'otp_hash' => Hash::make('123456'),
            'delivery_method' => MfaChallenge::METHOD_EMAIL,
            'expires_at' => $expiredAt,
            'attempt_count' => 0,
            'status' => MfaChallenge::STATUS_PENDING,
        ]);

        $response = $this->withSession(['auth.otp' => [
            'challenge_id' => $expiredChallenge->getKey(),
            'user_id' => $user->id,
            'remember' => true,
            'expires_at' => $expiredAt->toIso8601String(),
        ]])->post(route('login.otp.resend'));

        $response
            ->assertRedirect(route('login.otp'))
            ->assertSessionHas('status', 'A new OTP code has been sent.')
            ->assertSessionHas('auth.otp.user_id', $user->id)
            ->assertSessionHas('auth.otp.remember', true);

        $this->assertSame(MfaChallenge::STATUS_EXPIRED, $expiredChallenge->refresh()->status);
        $replacement = MfaChallenge::where('user_id', $user->id)
            ->whereKeyNot($expiredChallenge->getKey())
            ->latest('mfa_challenge_id')
            ->firstOrFail();
        $this->assertSame(MfaChallenge::STATUS_PENDING, $replacement->status);
        $this->assertTrue($replacement->expires_at->isFuture());
    }
}
