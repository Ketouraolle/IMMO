<?php

namespace Tests\Feature;

use App\Livewire\VisitAvailabilityManager;
use App\Models\Property;
use App\Models\User;
use App\Models\VisitRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class VisitAvailabilityManagerTest extends TestCase
{
    use RefreshDatabase;

    private function property(): Property
    {
        $owner = User::factory()->create(['role' => 'owner']);

        return Property::create([
            'owner_id' => $owner->id,
            'name' => 'Villa Test',
            'address' => '1 Rue Test',
            'type' => 'house',
            'monthly_rent' => 150000,
            'status' => 'vacant',
        ]);
    }

    public function test_admin_adds_a_date_and_duplicates_are_rejected(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $property = $this->property();
        $date = today()->addDays(3)->toDateString();

        Livewire::actingAs($admin)
            ->test(VisitAvailabilityManager::class, ['property' => $property])
            ->set('date', $date)
            ->set('startTime', '09:00')
            ->set('endTime', '13:00')
            ->call('add')
            ->assertHasNoErrors()
            ->set('date', $date)
            ->call('add')
            ->assertHasErrors('date');

        $this->assertSame(1, $property->visitSlots()->count());
    }

    public function test_end_time_must_be_after_start_time(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        Livewire::actingAs($admin)
            ->test(VisitAvailabilityManager::class, ['property' => $this->property()])
            ->set('startTime', '14:00')
            ->set('endTime', '10:00')
            ->call('add')
            ->assertHasErrors('endTime');
    }

    public function test_date_with_bookings_cannot_be_removed(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $property = $this->property();
        $booked = $property->visitSlots()->create(['date' => today()->addDay()->toDateString(), 'start_time' => '09:00', 'end_time' => '12:00']);
        $empty = $property->visitSlots()->create(['date' => today()->addDays(2)->toDateString(), 'start_time' => '09:00', 'end_time' => '12:00']);

        VisitRequest::create([
            'property_id' => $property->id, 'visit_slot_id' => $booked->id, 'name' => 'V', 'email' => 'v@example.com',
            'phone' => '699000000', 'status' => 'new', 'visit_date' => $booked->date, 'visit_time' => '09:30',
        ]);

        Livewire::actingAs($admin)
            ->test(VisitAvailabilityManager::class, ['property' => $property])
            ->call('remove', $booked->id)
            ->assertHasErrors('slots')
            ->call('remove', $empty->id);

        $this->assertModelExists($booked);
        $this->assertModelMissing($empty);
    }

    public function test_non_admin_cannot_manage_visit_dates(): void
    {
        $owner = User::factory()->create(['role' => 'owner']);

        Livewire::actingAs($owner)
            ->test(VisitAvailabilityManager::class, ['property' => $this->property()])
            ->assertForbidden();
    }
}
