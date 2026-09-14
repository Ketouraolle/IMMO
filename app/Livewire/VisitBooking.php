<?php

namespace App\Livewire;

use App\Models\Property;
use App\Models\User;
use App\Models\VisitRequest;
use App\Models\VisitSlot;
use App\Notifications\VisitRequested;
use App\Services\MobileMoneySimulator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\Rule;
use Livewire\Component;

/**
 * Public visit booking on a listing. When the admin has published visit dates the visitor picks one
 * and a time inside its window; otherwise they propose any day and time. Then pay now or at the visit.
 */
class VisitBooking extends Component
{
    public Property $property;

    public ?int $slotId = null;   // published-dates mode
    public ?string $date = null;  // open mode: day proposed by the visitor (Y-m-d)
    public ?string $time = null;  // H:i, used by both modes

    public string $name = '';
    public string $email = '';
    public string $phone = '';
    public string $message = '';

    public string $paymentOption = 'pay_now';
    public ?string $payMethod = null;
    public string $payPhone = '';

    public ?int $bookedId = null;

    public function selectSlot(int $slotId): void
    {
        $this->slotId = $slotId;
        $this->time = null;
        $this->resetErrorBag(['slotId', 'time']);
    }

    public function selectTime(string $time): void
    {
        $this->time = $time;
        $this->resetErrorBag('time');
    }

    public function updatedDate(): void
    {
        $this->resetErrorBag(['date', 'time']);
    }

    public function updatedTime(): void
    {
        $this->resetErrorBag('time');
    }

    /** Validates everything except payment. Called by the payment widget before it starts the operator prompt. */
    public function validateDetails(): bool
    {
        $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['required', 'string', 'max:50'],
            'message' => ['nullable', 'string', 'max:1000'],
            'paymentOption' => ['required', Rule::in(['pay_now', 'pay_at_visit'])],
        ]);

        return $this->hasPublishedDates() ? $this->validateSlotChoice() : $this->validateOpenChoice();
    }

    public function book(): void
    {
        $limiterKey = 'visit-booking:'.request()->ip();
        if (RateLimiter::tooManyAttempts($limiterKey, 5)) {
            $this->addError('form', 'Too many booking attempts. Please wait a minute and try again.');
            return;
        }

        if (! $this->validateDetails()) {
            return;
        }

        $fee = (float) $this->property->visit_fee;
        $payNow = $fee > 0 && $this->paymentOption === 'pay_now';

        if ($payNow) {
            $this->validate([
                'payMethod' => ['required', Rule::in(array_keys(MobileMoneySimulator::OPERATORS))],
                'payPhone' => ['required', MobileMoneySimulator::PHONE_RULE],
            ]);
        }

        $slot = $this->hasPublishedDates() ? $this->slot() : null;

        $visit = VisitRequest::create([
            'property_id' => $this->property->id,
            'visit_slot_id' => $slot?->id,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'message' => $this->message ?: null,
            'status' => 'new',
            'visit_date' => $slot?->date ?? $this->date,
            'visit_time' => $this->time,
            'fee_amount' => $fee,
            'payment_option' => $fee > 0 ? $this->paymentOption : null,
            'payment_status' => $fee <= 0 ? 'not_required' : ($payNow ? 'paid' : 'unpaid'),
            'payment_method' => $payNow ? $this->payMethod : null,
            'transaction_ref' => $payNow ? app(MobileMoneySimulator::class)->charge($this->payMethod, $this->payPhone, $fee) : null,
            'paid_at' => $payNow ? now() : null,
        ]);

        RateLimiter::hit($limiterKey, 60);

        Notification::send(User::activeAdmins()->get(), new VisitRequested($visit));

        $this->bookedId = $visit->id;
    }

    public function startOver(): void
    {
        $this->reset('slotId', 'date', 'time', 'message', 'payMethod', 'payPhone', 'bookedId');
        $this->paymentOption = 'pay_now';
    }

    protected function validationAttributes(): array
    {
        return [
            'payMethod' => 'operator',
            'payPhone' => 'mobile money number',
            'paymentOption' => 'payment option',
            'date' => 'day',
        ];
    }

    private function validateSlotChoice(): bool
    {
        $slot = $this->slot();
        if (! $slot) {
            $this->addError('slotId', 'Pick one of the available dates.');
            return false;
        }

        if (! in_array($this->time, $slot->timeOptions(), true)
            || in_array($this->time, $slot->bookedTimes(), true)
            || $slot->isTimeInPast($this->time)) {
            $this->addError('time', 'That time is no longer available. Please pick another.');
            return false;
        }

        return true;
    }

    private function validateOpenChoice(): bool
    {
        $this->validate([
            'date' => ['required', 'date_format:Y-m-d', 'after_or_equal:today'],
            'time' => ['required', 'date_format:H:i'],
        ], [
            'date.required' => 'Pick a day for your visit.',
            'date.after_or_equal' => 'Pick today or a later day.',
            'time.required' => 'Pick a time for your visit.',
        ]);

        if (Carbon::parse("{$this->date} {$this->time}")->isPast()) {
            $this->addError('time', 'That time has already passed. Please pick a later time.');
            return false;
        }

        $taken = VisitRequest::where('property_id', $this->property->id)
            ->whereDate('visit_date', $this->date)
            ->where('visit_time', $this->time)
            ->where('status', '!=', 'cancelled')
            ->exists();

        if ($taken) {
            $this->addError('time', 'Someone already booked that time. Please pick another.');
            return false;
        }

        return true;
    }

    /** Published dates that still have at least one free time; fully booked or elapsed dates are hidden. */
    private function bookableSlots(): \Illuminate\Support\Collection
    {
        return $this->property->upcomingVisitSlots()->get()
            ->filter(fn (VisitSlot $slot) => $slot->availableTimes() !== [])
            ->values();
    }

    // With nothing bookable left, the visitor falls back to proposing any day and time
    private function hasPublishedDates(): bool
    {
        return $this->bookableSlots()->isNotEmpty();
    }

    private function slot(): ?VisitSlot
    {
        return $this->slotId ? $this->bookableSlots()->firstWhere('id', $this->slotId) : null;
    }

    public function render()
    {
        $slots = $this->bookableSlots();
        $slot = $slots->firstWhere('id', $this->slotId);

        return view('livewire.visit-booking', [
            'slots' => $slots,
            'slot' => $slot,
            'times' => $slot?->timeOptions() ?? [],
            'unavailable' => $slot ? array_values(array_diff($slot->timeOptions(), $slot->availableTimes())) : [],
            'fee' => (float) $this->property->visit_fee,
            'booking' => $this->bookedId ? VisitRequest::find($this->bookedId) : null,
        ]);
    }
}
