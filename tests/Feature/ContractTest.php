<?php

namespace Tests\Feature;

use App\Models\Contract;
use App\Models\Lease;
use App\Models\Property;
use App\Models\User;
use App\Notifications\ContractReadyToSign;
use App\Notifications\ContractSigned;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class ContractTest extends TestCase
{
    use RefreshDatabase;

    // 1×1 transparent PNG
    private const PNG = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNkYPhfDwAChwGA60e6kgAAAABJRU5ErkJggg==';

    private User $admin;
    private User $tenant;
    private User $owner;
    private Lease $lease;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->twoFactorEnabled()->create(['role' => 'admin']);
        $this->tenant = User::factory()->create(['role' => 'tenant']);
        $this->owner = User::factory()->create(['role' => 'owner']);

        $property = Property::create([
            'owner_id' => $this->owner->id, 'name' => 'Résidence Test', 'address' => '1 Rue Test', 'type' => 'apartment',
            'monthly_rent' => 250000, 'status' => 'occupied',
        ]);

        $this->lease = Lease::create([
            'property_id' => $property->id, 'tenant_id' => $this->tenant->id, 'start_date' => now(),
            'rent_amount' => 250000, 'billing_cycle' => 'monthly', 'status' => 'active',
        ]);
    }

    private function sendContract(): Contract
    {
        $this->actingAs($this->admin)->post(route('contracts.store', $this->lease), [
            'special_conditions' => 'Deposit: two months of rent.',
            'send' => 1,
        ]);

        return Contract::sole();
    }

    public function test_admin_previews_then_sends_contract_and_tenant_is_notified(): void
    {
        Notification::fake();

        $this->actingAs($this->admin)->get(route('contracts.create', $this->lease))
            ->assertOk()
            ->assertSee('Residential Lease Agreement');

        $contract = $this->sendContract();

        $this->assertTrue($contract->isSent());
        $this->assertStringContainsString('Deposit: two months of rent.', $contract->body);
        $this->assertSame(hash('sha256', $contract->body), $contract->body_hash);
        Notification::assertSentTo($this->tenant, ContractReadyToSign::class);
    }

    public function test_draft_is_not_visible_to_the_tenant(): void
    {
        $this->actingAs($this->admin)->post(route('contracts.store', $this->lease), ['special_conditions' => null]);
        $contract = Contract::sole();

        $this->assertTrue($contract->isDraft());
        $this->actingAs($this->tenant)->get(route('contracts.show', $contract))->assertForbidden();
    }

    public function test_tenant_signs_and_admins_are_notified(): void
    {
        Notification::fake();
        $contract = $this->sendContract();

        $this->actingAs($this->tenant)->get(route('contracts.show', $contract))
            ->assertOk()
            ->assertSee('Sign this contract');

        $this->actingAs($this->tenant)->post(route('contracts.sign', $contract), [
            'agree' => '1',
            'signature' => self::PNG,
        ])->assertRedirect(route('contracts.show', $contract));

        $contract->refresh();
        $this->assertTrue($contract->isSigned());
        $this->assertNotNull($contract->signed_at);
        $this->assertSame(self::PNG, $contract->signature_data);
        $this->assertNotNull($contract->signer_ip);
        Notification::assertSentTo($this->admin, ContractSigned::class);

        // Signed contracts are final
        $this->actingAs($this->tenant)->post(route('contracts.sign', $contract), ['agree' => '1', 'signature' => self::PNG])
            ->assertStatus(400);
        $this->actingAs($this->admin)->get(route('contracts.create', $this->lease))->assertForbidden();

        $this->actingAs($this->owner)->get(route('contracts.show', $contract))->assertOk()->assertSee('Audit trail');
    }

    public function test_signature_requires_agreement_and_a_real_png(): void
    {
        $contract = $this->sendContract();

        $this->actingAs($this->tenant)->post(route('contracts.sign', $contract), ['signature' => self::PNG])
            ->assertSessionHasErrors('agree');

        $this->actingAs($this->tenant)->post(route('contracts.sign', $contract), [
            'agree' => '1',
            'signature' => 'data:image/png;base64,'.base64_encode('not an image'),
        ])->assertSessionHasErrors('signature');

        $this->assertTrue($contract->fresh()->isSent());
    }

    public function test_another_tenant_cannot_view_or_sign(): void
    {
        $contract = $this->sendContract();
        $stranger = User::factory()->create(['role' => 'tenant']);

        $this->actingAs($stranger)->get(route('contracts.show', $contract))->assertForbidden();
        $this->actingAs($stranger)->post(route('contracts.sign', $contract), ['agree' => '1', 'signature' => self::PNG])
            ->assertForbidden();
    }

    public function test_my_contract_shortcut(): void
    {
        $this->actingAs($this->tenant)->get(route('contracts.mine'))->assertOk()->assertSee('No contract yet');

        $contract = $this->sendContract();

        $this->actingAs($this->tenant)->get(route('contracts.mine'))->assertRedirect(route('contracts.show', $contract));
    }
}
