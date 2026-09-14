<?php

namespace Tests\Feature;

use App\Models\Lease;
use App\Models\Payment;
use App\Models\Property;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RentPaymentTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $tenant;
    private Property $property;
    private Lease $lease;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->twoFactorEnabled()->create(['role' => 'admin']);
        $this->tenant = User::factory()->create(['role' => 'tenant', 'phone' => '+237 6 77 11 22 33']);
        $owner = User::factory()->create(['role' => 'owner']);

        $this->property = Property::create([
            'owner_id' => $owner->id, 'name' => 'Résidence Test', 'address' => '1 Rue Test', 'type' => 'apartment',
            'monthly_rent' => 250000, 'commission_rate' => 10, 'status' => 'occupied',
        ]);

        $this->lease = Lease::create([
            'property_id' => $this->property->id, 'tenant_id' => $this->tenant->id, 'start_date' => now()->subMonth(),
            'rent_amount' => 250000, 'billing_cycle' => 'monthly', 'status' => 'active',
        ]);
    }

    public function test_tenant_mobile_money_payment_is_approved_with_receipt_and_commission(): void
    {
        $response = $this->actingAs($this->tenant)->post(route('payments.submit'), [
            'amount' => 250000,
            'period_covered' => 'September 2026',
            'method' => 'mtn_momo',
            'phone' => '+237 6 77 11 22 33',
        ]);

        $payment = Payment::sole();
        $response->assertRedirect(route('payments.receipt', $payment));

        $this->assertTrue($payment->isApproved());
        $this->assertNotNull($payment->receipt_number);
        $this->assertNull($payment->recorded_by);
        $this->assertStringStartsWith('MOMO-', $payment->transaction_ref);
        $this->assertEquals(10, $payment->commission_rate);
        $this->assertEquals(25000, $payment->commission_amount);
        $this->assertEquals(225000, $payment->netAmount());

        $this->actingAs($this->tenant)->get(route('payments.receipt', $payment))
            ->assertOk()
            ->assertSee('MTN MoMo')
            ->assertSee($payment->transaction_ref);
    }

    public function test_tenant_must_use_a_mobile_money_operator(): void
    {
        $this->actingAs($this->tenant)->post(route('payments.submit'), [
            'amount' => 250000, 'method' => 'cash', 'phone' => '677112233',
        ])->assertSessionHasErrors('method');

        $this->assertSame(0, Payment::count());
    }

    public function test_admin_approval_snapshots_commission(): void
    {
        $payment = $this->lease->payments()->create([
            'submitted_by' => $this->tenant->id, 'amount' => 250000, 'paid_on' => today(), 'method' => 'mobile_money', 'status' => 'pending',
        ]);

        $this->actingAs($this->admin)->post(route('payments.approve', $payment))->assertRedirect();

        $this->property->update(['commission_rate' => 20]);
        $payment->refresh();

        $this->assertTrue($payment->isApproved());
        $this->assertEquals(25000, $payment->commission_amount);
        $this->assertSame($this->admin->id, $payment->recorded_by);
    }

    public function test_receipt_numbers_stay_unique_when_older_payments_are_approved_later(): void
    {
        $older = $this->lease->payments()->create(['amount' => 1000, 'paid_on' => today(), 'method' => 'cash', 'status' => 'pending']);
        $newer = $this->lease->payments()->create(['amount' => 1000, 'paid_on' => today(), 'method' => 'cash', 'status' => 'pending']);

        $newer->markApproved($this->admin);
        $older->markApproved($this->admin);

        $this->assertNotSame($older->receipt_number, $newer->receipt_number);
    }
}
