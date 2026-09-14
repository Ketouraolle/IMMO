<?php

namespace Tests\Feature;

use App\Livewire\VisitBooking;
use App\Models\Property;
use App\Models\User;
use App\Models\VisitRequest;
use App\Models\VisitSlot;
use App\Notifications\VisitRequested;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;
use Tests\TestCase;

class VisitBookingTest extends TestCase
{
    use RefreshDatabase;

    /** @return array{Property, VisitSlot} */
    private function propertyWithSlot(float $fee = 5000): array
    {
        $owner = User::factory()->create(['role' => 'owner']);

        $property = Property::create([
            'owner_id' => $owner->id,
            'name' => 'Villa Test',
            'address' => '1 Rue Test',
            'city' => 'Douala',
            'type' => 'house',
            'monthly_rent' => 150000,
            'visit_fee' => $fee,
            'status' => 'vacant',
        ]);

        $slot = $property->visitSlots()->create([
            'date' => today()->addDays(2)->toDateString(),
            'start_time' => '09:00',
            'end_time' => '12:00',
        ]);

        return [$property, $slot];
    }

    private function bookingFor(Property $property, VisitSlot $slot, string $time = '10:30')
    {
        return Livewire::test(VisitBooking::class, ['property' => $property])
            ->call('selectSlot', $slot->id)
            ->call('selectTime', $time)
            ->set('name', 'Jean Ondoa')
            ->set('email', 'jean@example.com')
            ->set('phone', '+237 699 88 77 66');
    }

    public function test_pay_at_visit_creates_unpaid_request_and_notifies_admins(): void
    {
        Notification::fake();
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $inactiveAdmin = User::factory()->create(['role' => 'admin', 'is_active' => false]);
        [$property, $slot] = $this->propertyWithSlot();

        $this->bookingFor($property, $slot)
            ->set('paymentOption', 'pay_at_visit')
            ->call('book')
            ->assertHasNoErrors()
            ->assertSet('bookedId', fn ($id) => $id !== null);

        $visit = VisitRequest::sole();
        $this->assertSame('unpaid', $visit->payment_status);
        $this->assertSame('10:30', $visit->visitTimeLabel());
        $this->assertEquals(5000, $visit->fee_amount);
        $this->assertSame($slot->id, $visit->visit_slot_id);

        Notification::assertSentTo($admin, VisitRequested::class);
        Notification::assertNotSentTo($inactiveAdmin, VisitRequested::class);
    }

    public function test_pay_now_marks_request_paid_with_operator_reference(): void
    {
        Notification::fake();
        [$property, $slot] = $this->propertyWithSlot();

        $this->bookingFor($property, $slot)
            ->set('paymentOption', 'pay_now')
            ->set('payMethod', 'orange_money')
            ->set('payPhone', '677 11 22 33')
            ->call('book')
            ->assertHasNoErrors();

        $visit = VisitRequest::sole();
        $this->assertSame('paid', $visit->payment_status);
        $this->assertSame('orange_money', $visit->payment_method);
        $this->assertStringStartsWith('OM-', $visit->transaction_ref);
        $this->assertNotNull($visit->paid_at);
    }

    public function test_pay_now_requires_a_valid_mobile_number(): void
    {
        [$property, $slot] = $this->propertyWithSlot();

        $this->bookingFor($property, $slot)
            ->set('payMethod', 'mtn_momo')
            ->set('payPhone', '12345')
            ->call('book')
            ->assertHasErrors('payPhone');

        $this->assertSame(0, VisitRequest::count());
    }

    public function test_time_outside_the_window_is_rejected(): void
    {
        [$property, $slot] = $this->propertyWithSlot();

        $this->bookingFor($property, $slot, '15:00')
            ->set('paymentOption', 'pay_at_visit')
            ->call('book')
            ->assertHasErrors('time');

        $this->assertSame(0, VisitRequest::count());
    }

    public function test_a_booked_time_cannot_be_taken_twice(): void
    {
        [$property, $slot] = $this->propertyWithSlot();

        VisitRequest::create([
            'property_id' => $property->id,
            'visit_slot_id' => $slot->id,
            'name' => 'First visitor',
            'email' => 'first@example.com',
            'phone' => '699000000',
            'status' => 'new',
            'visit_date' => $slot->date,
            'visit_time' => '10:00',
        ]);

        $this->bookingFor($property, $slot, '10:00')
            ->set('paymentOption', 'pay_at_visit')
            ->call('book')
            ->assertHasErrors('time');

        $this->assertSame(1, VisitRequest::count());
    }

    public function test_free_visit_needs_no_payment(): void
    {
        Notification::fake();
        [$property, $slot] = $this->propertyWithSlot(fee: 0);

        $this->bookingFor($property, $slot)
            ->call('book')
            ->assertHasNoErrors();

        $visit = VisitRequest::sole();
        $this->assertSame('not_required', $visit->payment_status);
        $this->assertNull($visit->payment_option);
    }

    public function test_listing_page_renders_the_booking_widget(): void
    {
        [$property] = $this->propertyWithSlot();

        $this->get(route('public.properties.show', $property))
            ->assertOk()
            ->assertSee('Book a visit')
            ->assertSee('Pick a date');
    }
}
