<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    public const METHODS = [
        'orange_money' => 'Orange Money',
        'mtn_momo' => 'MTN MoMo',
        'cash' => 'Cash',
        'bank_transfer' => 'Bank transfer',
        'mobile_money' => 'Mobile Money',
        'other' => 'Other',
    ];

    protected $fillable = [
        'lease_id', 'submitted_by', 'recorded_by', 'receipt_number', 'amount', 'commission_rate', 'commission_amount',
        'paid_on', 'period_covered', 'method', 'transaction_ref', 'status', 'rejection_reason', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'paid_on' => 'date',
            'amount' => 'decimal:2',
            'commission_rate' => 'decimal:2',
            'commission_amount' => 'decimal:2',
        ];
    }

    public function lease()
    {
        return $this->belongsTo(Lease::class);
    }

    public function recordedBy()
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    public function submittedBy()
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }

    public function isPending(): bool { return $this->status === 'pending'; }
    public function isApproved(): bool { return $this->status === 'approved'; }
    public function isRejected(): bool { return $this->status === 'rejected'; }

    public function methodLabel(): string
    {
        return __(self::METHODS[$this->method] ?? ucfirst(str_replace('_', ' ', $this->method)));
    }

    public function netAmount(): float
    {
        return (float) $this->amount - (float) $this->commission_amount;
    }

    /**
     * Approve a saved payment: issue its receipt and snapshot the property's commission.
     * $by is null when the payment was confirmed by the mobile money operator rather than an admin.
     */
    public function markApproved(?User $by): void
    {
        $rate = (float) $this->lease->property->commission_rate;

        $this->update([
            'status' => 'approved',
            'recorded_by' => $by?->id,
            'receipt_number' => $this->receipt_number ?? 'RCT-'.now()->format('Ym').'-'.str_pad($this->id, 5, '0', STR_PAD_LEFT),
            'commission_rate' => $rate,
            'commission_amount' => round((float) $this->amount * $rate / 100),
        ]);
    }
}
