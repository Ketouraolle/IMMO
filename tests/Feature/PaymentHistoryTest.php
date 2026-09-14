<?php

namespace Tests\Feature;

use App\Models\Lease;
use App\Models\Payment;
use App\Models\Property;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class PaymentHistoryTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $owner;
    private User $otherOwner;
    private User $tenant;
    private User $otherTenant;
    private Property $property;
    private Lease $lease;
    private Lease $otherLease;

    protected function setUp(): void
    {
        parent::setUp();
        $this->travelTo(Carbon::parse('2026-09-14 12:00'));

        $this->admin = User::factory()->twoFactorEnabled()->create(['role' => 'admin']);
        $this->owner = User::factory()->create(['role' => 'owner']);
        $this->otherOwner = User::factory()->create(['role' => 'owner']);
        $this->tenant = User::factory()->create(['role' => 'tenant', 'name' => 'Brice Mvondo']);
        $this->otherTenant = User::factory()->create(['role' => 'tenant', 'name' => 'Stephanie Ateba']);

        $this->property = Property::create([
            'owner_id' => $this->owner->id, 'name' => 'Résidence Bonapriso', 'address' => '1 Rue Test', 'type' => 'apartment',
            'monthly_rent' => 100000, 'commission_rate' => 10, 'status' => 'occupied',
        ]);
        $otherProperty = Property::create([
            'owner_id' => $this->otherOwner->id, 'name' => 'Studio Akwa', 'address' => '2 Rue Test', 'type' => 'studio',
            'monthly_rent' => 50000, 'commission_rate' => 10, 'status' => 'occupied',
        ]);

        $this->lease = Lease::create([
            'property_id' => $this->property->id, 'tenant_id' => $this->tenant->id, 'start_date' => '2026-06-14',
            'rent_amount' => 100000, 'billing_cycle' => 'monthly', 'status' => 'active',
        ]);
        $this->otherLease = Lease::create([
            'property_id' => $otherProperty->id, 'tenant_id' => $this->otherTenant->id, 'start_date' => '2026-09-01',
            'rent_amount' => 50000, 'billing_cycle' => 'monthly', 'status' => 'active',
        ]);

        $this->pay($this->lease, 100000, '2026-06-15', 'orange_money', 'approved', 'OM-AAA111');
        $this->pay($this->lease, 100000, '2026-07-15', 'cash', 'approved');
        $this->pay($this->lease, 100000, '2026-08-15', 'mobile_money', 'rejected');
        $this->pay($this->lease, 100000, '2026-09-10', 'mtn_momo', 'pending');
        $this->pay($this->otherLease, 50000, '2026-09-02', 'orange_money', 'approved', 'OM-BBB222');
    }

    private function pay(Lease $lease, int $amount, string $date, string $method, string $status, ?string $ref = null): Payment
    {
        $payment = $lease->payments()->create([
            'amount' => $amount, 'paid_on' => $date, 'method' => $method, 'transaction_ref' => $ref,
            'status' => $status === 'approved' ? 'pending' : $status, 'period_covered' => Carbon::parse($date)->format('F Y'),
        ]);

        if ($status === 'approved') {
            $payment->markApproved($this->admin);
        }

        return $payment;
    }

    public function test_admin_sees_every_payment_with_totals_and_summary(): void
    {
        $this->actingAs($this->admin)->get(route('payments.index'))
            ->assertOk()
            ->assertSee('Brice Mvondo')
            ->assertSee('Stephanie Ateba')
            ->assertSee('Export CSV')
            ->assertViewHas('totals', fn ($totals) => (int) $totals->count === 3 && (float) $totals->amount === 250000.0 && (float) $totals->commission === 25000.0)
            ->assertViewHas('stats', fn ($stats) => $stats['monthAmount'] === 50000.0 && $stats['pendingCount'] === 1 && $stats['yearAmount'] === 250000.0);
    }

    public function test_filters_combine(): void
    {
        $count = fn (array $query) => $this->actingAs($this->admin)->get(route('payments.index', $query))->viewData('payments')->total();

        $this->assertSame(1, $count(['status' => 'approved', 'method' => 'cash']));
        $this->assertSame(2, $count(['from' => '2026-09-01', 'to' => '2026-09-30']));
        $this->assertSame(2, $count(['from' => '2026-09-30', 'to' => '2026-09-01'])); // swapped range still works
        $this->assertSame(1, $count(['q' => 'OM-AAA']));
        $this->assertSame(4, $count(['q' => 'brice'])); // case-insensitive tenant name
        $this->assertSame(1, $count(['q' => 'akwa']));
        $this->assertSame(4, $count(['property' => (string) $this->property->id]));
        $this->assertSame(5, $count(['status' => 'bogus', 'from' => 'not-a-date', 'method' => 'barter'])); // invalid values ignored
    }

    public function test_owner_only_sees_their_properties(): void
    {
        $response = $this->actingAs($this->otherOwner)->get(route('payments.index'));
        $response->assertOk()->assertSee('Stephanie Ateba')->assertDontSee('Brice Mvondo');
        $this->assertSame(1, $response->viewData('payments')->total());

        // Filtering by someone else's property shows nothing
        $this->assertSame(0, $this->actingAs($this->otherOwner)->get(route('payments.index', ['property' => $this->property->id]))->viewData('payments')->total());
    }

    public function test_export_respects_filters_and_role(): void
    {
        $csv = $this->actingAs($this->admin)->get(route('payments.export', ['status' => 'approved']))
            ->assertOk()
            ->assertHeader('content-type', 'text/csv; charset=UTF-8')
            ->streamedContent();

        $lines = array_values(array_filter(explode("\n", trim($csv))));
        $this->assertCount(4, $lines); // header + 3 approved
        $this->assertStringContainsString('Commission (XAF)', $lines[0]);

        $tenantCsv = $this->actingAs($this->otherTenant)->get(route('payments.export'))->streamedContent();
        $this->assertStringNotContainsString('Commission', $tenantCsv);
        $this->assertStringContainsString('OM-BBB222', $tenantCsv);
        $this->assertStringNotContainsString('OM-AAA111', $tenantCsv);
    }

    public function test_tenant_behind_on_rent_sees_what_is_due(): void
    {
        // Lease started 14 Jun, two approved months → paid until 14 Aug; on 14 Sep two periods are due
        $this->assertSame(2, $this->lease->paidPeriods());
        $this->assertSame('2026-08-14', $this->lease->nextDueDate()->toDateString());
        $this->assertSame(2, $this->lease->periodsDue());
        $this->assertSame(200000.0, $this->lease->amountDue());

        $this->actingAs($this->tenant)->get(route('payments.index'))
            ->assertOk()
            ->assertSee('Payment history')
            ->assertSee('Rent due for 2 periods')
            ->assertSee('200,000 XAF')
            ->assertDontSee('Stephanie Ateba');

        $this->actingAs($this->tenant)->get(route('payments.submit-form'))
            ->assertOk()
            ->assertSee('value="200000"', false)
            ->assertSee('August 2026 – September 2026');
    }

    public function test_tenant_up_to_date_sees_next_payment_with_credit(): void
    {
        $this->pay($this->otherLease, 30000, '2026-09-05', 'orange_money', 'approved');
        $lease = $this->otherLease->fresh();

        $this->assertSame(0, $lease->periodsDue());
        $this->assertSame(30000.0, $lease->credit());
        $this->assertSame(20000.0, $lease->suggestedPayment());

        $this->actingAs($this->otherTenant)->get(route('payments.index'))
            ->assertOk()
            ->assertSee('Next payment')
            ->assertSee('Due 1 October 2026')
            ->assertSee('Includes 30,000 XAF already paid toward this period.');
    }
}
