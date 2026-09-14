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
        $admin = User::factory()->twoFactorEnabled()->create(['role' => 'admin', 'is_active' => true]);
        $inactiveAdmin = User::factory()->twoFactorEnabled()->create(['role' => 'admin', 'is_active' => false]);
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

    private function propertyWithoutSlots(float $fee = 5000): Property
    {
        [$property, $slot] = $this->propertyWithSlot($fee);
        $slot->delete();

        return $property;
    }

    public function test_visitor_picks_any_day_and_time_when_no_dates_are_published(): void
    {
        Notification::fake();
        $admin = User::factory()->twoFactorEnabled()->create(['role' => 'admin']);
        $property = $this->propertyWithoutSlots();
        $day = today()->addDays(4)->toDateString();

        Livewire::test(VisitBooking::class, ['property' => $property])
            ->set('date', $day)
            ->set('time', '14:15')
            ->set('name', 'Jean Ondoa')
            ->set('email', 'jean@example.com')
            ->set('phone', '+237 699 88 77 66')
            ->set('paymentOption', 'pay_at_visit')
            ->call('book')
            ->assertHasNoErrors();

        $visit = VisitRequest::sole();
        $this->assertNull($visit->visit_slot_id);
        $this->assertSame($day, $visit->visit_date->toDateString());
        $this->assertSame('14:15', $visit->visitTimeLabel());
        Notification::assertSentTo($admin, VisitRequested::class);
    }

    public function test_open_booking_rejects_past_and_already_taken_times(): void
    {
        $property = $this->propertyWithoutSlots(fee: 0);
        $day = today()->addDays(2)->toDateString();

        VisitRequest::create([
            'property_id' => $property->id, 'name' => 'First visitor', 'email' => 'first@example.com', 'phone' => '699000000',
            'status' => 'new', 'visit_date' => $day, 'visit_time' => '10:00',
        ]);

        $component = Livewire::test(VisitBooking::class, ['property' => $property])
            ->set('name', 'Jean Ondoa')
            ->set('email', 'jean@example.com')
            ->set('phone', '699887766');

        $component->set('date', today()->subDay()->toDateString())->set('time', '10:00')->call('book')->assertHasErrors('date');
        $component->set('date', $day)->set('time', '10:00')->call('book')->assertHasErrors('time');
        $component->set('time', '10:30')->call('book')->assertHasNoErrors();

        $this->assertSame(2, VisitRequest::count());
    }

    public function test_published_dates_must_be_used_when_they_exist(): void
    {
        [$property] = $this->propertyWithSlot();

        Livewire::test(VisitBooking::class, ['property' => $property])
            ->set('date', today()->addDays(5)->toDateString())
            ->set('time', '10:00')
            ->set('name', 'Jean Ondoa')
            ->set('email', 'jean@example.com')
            ->set('phone', '699887766')
            ->set('paymentOption', 'pay_at_visit')
            ->call('book')
            ->assertHasErrors('slotId');

        $this->assertSame(0, VisitRequest::count());
    }

    public function test_falls_back_to_free_choice_when_published_dates_are_fully_booked(): void
    {
        Notification::fake();
        [$property, $slot] = $this->propertyWithSlot(fee: 0);
        $slot->update(['start_time' => '09:00', 'end_time' => '09:30']); // a single bookable time

        VisitRequest::create([
            'property_id' => $property->id, 'visit_slot_id' => $slot->id, 'name' => 'First visitor', 'email' => 'first@example.com',
            'phone' => '699000000', 'status' => 'new', 'visit_date' => $slot->date, 'visit_time' => '09:00',
        ]);

        $this->get(route('public.properties.show', $property))->assertOk()->assertSee('Pick a day and time');

        Livewire::test(VisitBooking::class, ['property' => $property])
            ->set('date', today()->addDays(6)->toDateString())
            ->set('time', '16:00')
            ->set('name', 'Jean Ondoa')
            ->set('email', 'jean@example.com')
            ->set('phone', '699887766')
            ->call('book')
            ->assertHasNoErrors();

        $this->assertNull(VisitRequest::latest('id')->first()->visit_slot_id);
    }

    public function test_listing_without_published_dates_offers_free_choice(): void
    {
        $property = $this->propertyWithoutSlots();

        $this->get(route('public.properties.show', $property))
            ->assertOk()
            ->assertSee('Pick a day and time');
    }
}
