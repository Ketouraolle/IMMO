<?php

namespace App\Livewire;

use App\Models\Property;
use Illuminate\Support\Carbon;
use Livewire\Component;

class VisitAvailabilityManager extends Component
{
    public Property $property;

    public string $date = '';
    public string $startTime = '09:00';
    public string $endTime = '17:00';

    public function mount(Property $property): void
    {
        $this->ensureAdmin();
        $this->property = $property;
        $this->date = today()->addDay()->toDateString();
    }

    public function add(): void
    {
        $this->ensureAdmin();

        $this->validate([
            'date' => ['required', 'date', 'after_or_equal:today'],
            'startTime' => ['required', 'date_format:H:i'],
            'endTime' => ['required', 'date_format:H:i', 'after:startTime'],
        ], [
            'date.after_or_equal' => __('Pick today or a future date.'),
            'endTime.after' => __('End time must be after the start time.'),
        ]);

        if ($this->property->visitSlots()->whereDate('date', $this->date)->exists()) {
            $this->addError('date', __('This date is already open for visits.'));
            return;
        }

        $this->property->visitSlots()->create([
            'date' => $this->date,
            'start_time' => $this->startTime,
            'end_time' => $this->endTime,
        ]);

        // Pre-fill the next day so opening a run of dates is quick
        $this->date = Carbon::parse($this->date)->addDay()->toDateString();
        $this->dispatch('status', message: __('Visit date added.'));
    }

    public function toggle(int $slotId): void
    {
        $this->ensureAdmin();

        $slot = $this->property->visitSlots()->findOrFail($slotId);
        $slot->update(['is_active' => ! $slot->is_active]);
    }

    public function remove(int $slotId): void
    {
        $this->ensureAdmin();

        $slot = $this->property->visitSlots()->findOrFail($slotId);

        if ($slot->visitRequests()->where('status', '!=', 'cancelled')->exists()) {
            $this->addError('slots', __('That date has bookings. Cancel them first, or pause the date instead.'));
            return;
        }

        $slot->delete();
        $this->dispatch('status', message: __('Visit date removed.'));
    }

    private function ensureAdmin(): void
    {
        abort_unless(auth()->check() && auth()->user()->isAdmin(), 403);
    }

    public function render()
    {
        return view('livewire.visit-availability-manager', [
            'slots' => $this->property->visitSlots()
                ->whereDate('date', '>=', today())
                ->withCount(['visitRequests as bookings_count' => fn ($q) => $q->where('status', '!=', 'cancelled')])
                ->orderBy('date')
                ->get(),
        ]);
    }
}
