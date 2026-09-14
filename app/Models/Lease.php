<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class Lease extends Model
{
    use HasFactory;

    protected $fillable = [
        'property_id', 'tenant_id', 'start_date', 'end_date', 'rent_amount', 'billing_cycle', 'status', 'document_path',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'rent_amount' => 'decimal:2',
        ];
    }

    public function property()
    {
        return $this->belongsTo(Property::class);
    }

    public function tenant()
    {
        return $this->belongsTo(User::class, 'tenant_id');
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function contract()
    {
        return $this->hasOne(Contract::class);
    }

    public function totalPaid()
    {
        return $this->payments()->sum('amount');
    }

    // ---- Rent position: worked out from the lease start, billing cycle and approved payments ----

    public const CYCLE_MONTHS = ['monthly' => 1, 'quarterly' => 3, 'yearly' => 12];

    private ?float $approvedTotalCache = null;

    public function cycleMonths(): int
    {
        return self::CYCLE_MONTHS[$this->billing_cycle] ?? 1;
    }

    public function approvedTotal(): float
    {
        return $this->approvedTotalCache ??= (float) $this->payments()->where('payments.status', 'approved')->sum('amount');
    }

    /** Whole billing periods covered by approved payments since the lease started. */
    public function paidPeriods(): int
    {
        return (float) $this->rent_amount > 0 ? (int) floor($this->approvedTotal() / (float) $this->rent_amount) : 0;
    }

    /** Money paid beyond the last whole period, counted toward the next one. */
    public function credit(): float
    {
        return max(0, $this->approvedTotal() - $this->paidPeriods() * (float) $this->rent_amount);
    }

    /** Start of the first period that isn't fully paid. */
    public function nextDueDate(): Carbon
    {
        return $this->start_date->copy()->addMonthsNoOverflow($this->paidPeriods() * $this->cycleMonths());
    }

    /** Periods that have already started without being paid (0 when rent is paid ahead). */
    public function periodsDue(): int
    {
        $next = $this->nextDueDate();

        if ($next->isAfter(today())) {
            return 0;
        }

        return intdiv((int) floor($next->diffInMonths(today())), $this->cycleMonths()) + 1;
    }

    public function amountDue(): float
    {
        return max(0, $this->periodsDue() * (float) $this->rent_amount - $this->credit());
    }

    /** What the tenant should pay now: everything due, or the next period if they're up to date. */
    public function suggestedPayment(): float
    {
        return $this->amountDue() > 0 ? $this->amountDue() : max(1, (float) $this->rent_amount - $this->credit());
    }

    /** Months covered, e.g. "October 2026", or "October 2026 – December 2026" for a quarter or several periods. */
    public function periodLabel(Carbon $start, int $periods = 1): string
    {
        $end = $start->copy()->addMonthsNoOverflow($this->cycleMonths() * $periods - 1);
        $from = ucfirst($start->translatedFormat('F Y'));

        return $from === ucfirst($end->translatedFormat('F Y')) ? $from : $from.' – '.$end->translatedFormat('F Y');
    }

    /** Label for the payment being made now (all due periods, or the next one). */
    public function suggestedPeriodLabel(): string
    {
        return $this->periodLabel($this->nextDueDate(), max(1, $this->periodsDue()));
    }
}
