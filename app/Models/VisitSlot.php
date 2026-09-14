<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class VisitSlot extends Model
{
    public const STEP_MINUTES = 30;

    protected $fillable = ['property_id', 'date', 'start_time', 'end_time', 'is_active'];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'is_active' => 'boolean',
        ];
    }

    public function property()
    {
        return $this->belongsTo(Property::class);
    }

    public function visitRequests()
    {
        return $this->hasMany(VisitRequest::class);
    }

    /**
     * Bookable times ("H:i") in 30-minute steps from the window start, ending before the window end.
     */
    public function timeOptions(): array
    {
        $cursor = Carbon::parse($this->start_time);
        $end = Carbon::parse($this->end_time);
        $times = [];

        while ($cursor->lt($end)) {
            $times[] = $cursor->format('H:i');
            $cursor->addMinutes(self::STEP_MINUTES);
        }

        return $times;
    }

    public function isTimeInPast(string $time): bool
    {
        return $this->date->copy()->setTimeFromTimeString($time)->isPast();
    }

    /** Times already taken by a non-cancelled request, as "H:i". */
    public function bookedTimes(): array
    {
        return $this->visitRequests()
            ->where('status', '!=', 'cancelled')
            ->pluck('visit_time')
            ->map(fn ($t) => substr($t, 0, 5))
            ->all();
    }

    public function windowLabel(): string
    {
        return substr($this->start_time, 0, 5).' – '.substr($this->end_time, 0, 5);
    }
}
