<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\TwoFactorAuthenticator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PragmaRX\Google2FA\Google2FA;
use Tests\TestCase;

class TwoFactorAuthenticationTest extends TestCase
{
    use RefreshDatabase;

    private function currentCode(User $user): string
    {
        return (new Google2FA)->getCurrentOtp($user->fresh()->two_factor_secret);
    }

    public function test_admin_without_two_factor_is_sent_to_security_setup(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->get(route('dashboard'))->assertRedirect(route('security.show'));
        $this->actingAs($admin)->get(route('payments.index'))->assertRedirect(route('security.show'));
        $this->actingAs($admin)->get(route('security.show'))
            ->assertOk()
            ->assertSee('Two-factor authentication is required for admin accounts.');
    }

    public function test_owners_and_tenants_can_use_the_app_without_two_factor(): void
    {
        $tenant = User::factory()->create(['role' => 'tenant']);

        $this->actingAs($tenant)->get(route('dashboard'))->assertOk();
    }

    public function test_setup_requires_a_valid_code_and_issues_recovery_codes(): void
    {
        $tenant = User::factory()->create(['role' => 'tenant']);

        $this->actingAs($tenant)->post(route('security.two-factor.enable'))->assertRedirect(route('security.show'));
        $this->assertNotNull($tenant->fresh()->two_factor_secret);
        $this->assertFalse($tenant->fresh()->hasTwoFactorEnabled());

        $this->actingAs($tenant)->get(route('security.show'))
            ->assertOk()
            ->assertSee('Scan this QR code with your authenticator app')
            ->assertSee('<svg', false);

        $this->actingAs($tenant)->post(route('security.two-factor.confirm'), ['code' => '000000'])->assertSessionHasErrors('code');
        $this->assertFalse($tenant->fresh()->hasTwoFactorEnabled());

        $this->actingAs($tenant)->post(route('security.two-factor.confirm'), ['code' => $this->currentCode($tenant)])
            ->assertRedirect(route('security.show'))
            ->assertSessionHas('recovery_codes', fn ($codes) => count($codes) === 8);

        $tenant->refresh();
        $this->assertTrue($tenant->hasTwoFactorEnabled());
        $this->assertCount(8, $tenant->two_factor_recovery_codes);
        // Only hashes are stored
        $this->assertSame(64, strlen($tenant->two_factor_recovery_codes[0]));
    }

    public function test_login_asks_for_a_code_and_rejects_replay(): void
    {
        $user = User::factory()->twoFactorEnabled()->create(['role' => 'owner', 'email' => 'owner@example.com']);

        $this->post('/login', ['email' => 'owner@example.com', 'password' => 'password'])
            ->assertRedirect(route('two-factor.challenge'));
        $this->assertGuest();

        $this->get(route('two-factor.challenge'))->assertOk()->assertSee('Enter the 6-digit code from your authenticator app.');

        $this->post(route('two-factor.challenge'), ['code' => '000000'])->assertSessionHasErrors('code');
        $this->assertGuest();

        $code = $this->currentCode($user);
        $this->post(route('two-factor.challenge'), ['code' => $code])->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($user);

        // The same code can't be used for a second sign-in
        $this->post(route('logout'));
        $this->post('/login', ['email' => 'owner@example.com', 'password' => 'password']);
        $this->post(route('two-factor.challenge'), ['code' => $code])->assertSessionHasErrors('code');
        $this->assertGuest();
    }

    public function test_wrong_password_never_reaches_the_code_step(): void
    {
        User::factory()->twoFactorEnabled()->create(['email' => 'owner@example.com']);

        $this->post('/login', ['email' => 'owner@example.com', 'password' => 'wrong'])->assertSessionHasErrors('email');
        $this->get(route('two-factor.challenge'))->assertRedirect(route('login'));
    }

    public function test_recovery_code_signs_in_once(): void
    {
        $twoFactor = app(TwoFactorAuthenticator::class);
        $codes = $twoFactor->generateRecoveryCodes();
        $user = User::factory()->twoFactorEnabled()->create(['email' => 'tenant@example.com', 'role' => 'tenant']);
        $user->forceFill(['two_factor_recovery_codes' => $twoFactor->hashRecoveryCodes($codes)])->save();

        $this->post('/login', ['email' => 'tenant@example.com', 'password' => 'password']);
        $this->post(route('two-factor.challenge'), ['recovery_code' => strtoupper($codes[0])])
            ->assertRedirect(route('dashboard'))
            ->assertSessionHas('status');
        $this->assertAuthenticatedAs($user);
        $this->assertCount(7, $user->fresh()->two_factor_recovery_codes);

        $this->post(route('logout'));
        $this->post('/login', ['email' => 'tenant@example.com', 'password' => 'password']);
        $this->post(route('two-factor.challenge'), ['recovery_code' => $codes[0]])->assertSessionHasErrors('recovery_code');
        $this->assertGuest();
    }

    public function test_pending_login_expires(): void
    {
        User::factory()->twoFactorEnabled()->create(['email' => 'owner@example.com']);

        $this->post('/login', ['email' => 'owner@example.com', 'password' => 'password']);
        $this->travel(6)->minutes();

        $this->post(route('two-factor.challenge'), ['code' => '123456'])
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors('email');
    }

    public function test_turning_off_needs_the_password_and_is_blocked_for_admins(): void
    {
        $tenant = User::factory()->twoFactorEnabled()->create(['role' => 'tenant']);

        $this->actingAs($tenant)->delete(route('security.two-factor.disable'), ['password' => 'wrong'])->assertSessionHasErrors('password');
        $this->assertTrue($tenant->fresh()->hasTwoFactorEnabled());

        $this->actingAs($tenant)->delete(route('security.two-factor.disable'), ['password' => 'password'])->assertRedirect(route('security.show'));
        $this->assertFalse($tenant->fresh()->hasTwoFactorEnabled());

        $admin = User::factory()->twoFactorEnabled()->create(['role' => 'admin']);
        $this->actingAs($admin)->delete(route('security.two-factor.disable'), ['password' => 'password'])->assertForbidden();
        $this->assertTrue($admin->fresh()->hasTwoFactorEnabled());
    }

    public function test_recovery_codes_can_be_regenerated_with_the_password(): void
    {
        $owner = User::factory()->twoFactorEnabled()->create(['role' => 'owner']);

        $this->actingAs($owner)->post(route('security.two-factor.recovery-codes'), ['password' => 'wrong'])->assertSessionHasErrors('password');

        $this->actingAs($owner)->post(route('security.two-factor.recovery-codes'), ['password' => 'password'])
            ->assertSessionHas('recovery_codes', fn ($codes) => count($codes) === 8);
        $this->assertCount(8, $owner->fresh()->two_factor_recovery_codes);
    }

    public function test_admin_can_reset_someone_elses_two_factor(): void
    {
        $admin = User::factory()->twoFactorEnabled()->create(['role' => 'admin']);
        $tenant = User::factory()->twoFactorEnabled()->create(['role' => 'tenant']);

        $this->actingAs($tenant)->delete(route('users.reset-two-factor', $admin))->assertForbidden();

        $this->actingAs($admin)->delete(route('users.reset-two-factor', $tenant))->assertRedirect();
        $this->assertFalse($tenant->fresh()->hasTwoFactorEnabled());

        $this->actingAs($admin)->delete(route('users.reset-two-factor', $admin))->assertForbidden();
    }
}
