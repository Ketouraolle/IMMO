<?php

namespace Tests\Feature;

use App\Models\Lease;
use App\Models\Payment;
use App\Models\Property;
use App\Models\User;
use App\Models\VisitRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

// Smoke tests: every role's pages render inside the sidebar layout, plus admin visit handling.
class AppPagesTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $owner;
    private User $tenant;
    private Property $property;
    private VisitRequest $visit;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->owner = User::factory()->create(['role' => 'owner']);
        $this->tenant = User::factory()->create(['role' => 'tenant']);

        $this->property = Property::create([
            'owner_id' => $this->owner->id, 'name' => 'Résidence Test', 'address' => '1 Rue Test', 'type' => 'apartment',
            'monthly_rent' => 250000, 'visit_fee' => 5000, 'commission_rate' => 10, 'status' => 'occupied',
        ]);

        $lease = Lease::create([
            'property_id' => $this->property->id, 'tenant_id' => $this->tenant->id, 'start_date' => now()->subMonth(),
            'rent_amount' => 250000, 'billing_cycle' => 'monthly', 'status' => 'active',
        ]);

        $lease->payments()->create(['amount' => 250000, 'paid_on' => today(), 'method' => 'orange_money', 'status' => 'pending'])
            ->markApproved($this->admin);

        $slot = $this->property->visitSlots()->create(['date' => today()->addDay()->toDateString(), 'start_time' => '09:00', 'end_time' => '12:00']);

        $this->visit = VisitRequest::create([
            'property_id' => $this->property->id, 'visit_slot_id' => $slot->id, 'name' => 'Jean Ondoa', 'email' => 'jean@example.com',
            'phone' => '+237 699 88 77 66', 'status' => 'new', 'visit_date' => $slot->date, 'visit_time' => '10:00',
            'fee_amount' => 5000, 'payment_option' => 'pay_at_visit', 'payment_status' => 'unpaid',
        ]);
    }

    public function test_dashboards_render_for_every_role(): void
    {
        $this->actingAs($this->admin)->get(route('dashboard'))->assertOk()->assertSee('Upcoming visits')->assertSee('Jean Ondoa');
        $this->actingAs($this->owner)->get(route('dashboard'))->assertOk()->assertSee('Net to you this month')->assertSee('225,000 XAF');
        $this->actingAs($this->tenant)->get(route('dashboard'))->assertOk()->assertSee('Your lease');
    }

    public function test_main_pages_render(): void
    {
        foreach (['properties.index', 'payments.index', 'issues.index', 'users.index', 'visit-requests.index'] as $route) {
            $this->actingAs($this->admin)->get(route($route))->assertOk();
        }
        $this->actingAs($this->admin)->get(route('properties.show', $this->property))->assertOk()->assertSee('Visit availability');
        $this->actingAs($this->owner)->get(route('payments.index'))->assertOk()->assertSee('Net to owner');
        $this->actingAs($this->tenant)->get(route('payments.submit-form'))->assertOk()->assertSee('Orange Money');
    }

    public function test_visit_requests_are_admin_only(): void
    {
        $this->actingAs($this->owner)->get(route('visit-requests.index'))->assertForbidden();
        $this->actingAs($this->tenant)->get(route('visit-requests.show', $this->visit))->assertForbidden();
    }

    public function test_admin_confirms_visit_and_collects_fee_by_mobile_money(): void
    {
        $this->actingAs($this->admin)->get(route('visit-requests.show', $this->visit))->assertOk()->assertSee('To collect at the visit');

        $this->actingAs($this->admin)->put(route('visit-requests.update-status', $this->visit), ['status' => 'confirmed'])->assertRedirect();

        $this->actingAs($this->admin)->post(route('visit-requests.collect-fee', $this->visit), [
            'method' => 'orange_money', 'phone' => '699887766',
        ])->assertRedirect();

        $this->visit->refresh();
        $this->assertSame('confirmed', $this->visit->status);
        $this->assertTrue($this->visit->isPaid());
        $this->assertStringStartsWith('OM-', $this->visit->transaction_ref);

        // Nothing left to collect
        $this->actingAs($this->admin)->post(route('visit-requests.collect-fee', $this->visit), ['method' => 'cash'])->assertStatus(400);
    }

    public function test_admin_collects_fee_in_cash_without_a_phone(): void
    {
        $this->actingAs($this->admin)->post(route('visit-requests.collect-fee', $this->visit), ['method' => 'cash'])
            ->assertSessionHasNoErrors();

        $this->assertSame('cash', $this->visit->fresh()->payment_method);
        $this->assertNull($this->visit->fresh()->transaction_ref);
    }

    public function test_new_lease_leads_to_contract_preparation(): void
    {
        $vacant = Property::create([
            'owner_id' => $this->owner->id, 'name' => 'Studio Vide', 'address' => '2 Rue Test', 'type' => 'studio',
            'monthly_rent' => 100000, 'status' => 'vacant',
        ]);
        $newTenant = User::factory()->create(['role' => 'tenant']);

        $this->actingAs($this->admin)->post(route('leases.store', $vacant), [
            'tenant_id' => $newTenant->id, 'start_date' => today()->toDateString(), 'rent_amount' => 100000, 'billing_cycle' => 'monthly',
        ])->assertRedirect(route('contracts.create', Lease::where('property_id', $vacant->id)->sole()));
    }

    public function test_property_form_saves_visit_fee_and_commission(): void
    {
        $this->actingAs($this->admin)->put(route('properties.update', $this->property), [
            'owner_id' => $this->owner->id, 'name' => 'Résidence Test', 'address' => '1 Rue Test', 'type' => 'apartment',
            'monthly_rent' => 250000, 'visit_fee' => 7500, 'commission_rate' => 12.5, 'status' => 'occupied',
        ])->assertRedirect();

        $this->property->refresh();
        $this->assertEquals(7500, $this->property->visit_fee);
        $this->assertEquals(12.5, $this->property->commission_rate);

        $this->actingAs($this->admin)->put(route('properties.update', $this->property), [
            'owner_id' => $this->owner->id, 'name' => 'Résidence Test', 'address' => '1 Rue Test', 'type' => 'apartment',
            'monthly_rent' => 250000, 'visit_fee' => 0, 'commission_rate' => 150, 'status' => 'occupied',
        ])->assertSessionHasErrors('commission_rate');
    }
}
