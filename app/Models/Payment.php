<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'lease_id', 'submitted_by', 'recorded_by', 'receipt_number', 'amount', 'paid_on',
        'period_covered', 'method', 'status', 'rejection_reason', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'paid_on' => 'date',
            'amount' => 'decimal:2',
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

    public static function generateReceiptNumber(): string
    {
        return 'RCT-' . now()->format('Ym') . '-' . str_pad((static::max('id') + 1), 5, '0', STR_PAD_LEFT);
    }
}
