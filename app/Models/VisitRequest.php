<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VisitRequest extends Model
{
    use HasFactory;

    public const STATUSES = ['new' => 'New', 'confirmed' => 'Confirmed', 'completed' => 'Completed', 'cancelled' => 'Cancelled'];

    protected $fillable = [
        'property_id', 'visit_slot_id', 'name', 'email', 'phone', 'message', 'status', 'handled_by',
        'visit_date', 'visit_time', 'fee_amount', 'payment_option', 'payment_status', 'payment_method',
        'transaction_ref', 'paid_at',
    ];

    protected function casts(): array
    {
        return [
            'visit_date' => 'date',
            'fee_amount' => 'decimal:2',
            'paid_at' => 'datetime',
        ];
    }

    public function property()
    {
        return $this->belongsTo(Property::class);
    }

    public function slot()
    {
        return $this->belongsTo(VisitSlot::class, 'visit_slot_id');
    }

    public function handledBy()
    {
        return $this->belongsTo(User::class, 'handled_by');
    }

    public function isPaid(): bool { return $this->payment_status === 'paid'; }

    public function needsPayment(): bool { return $this->payment_status === 'unpaid' && $this->fee_amount > 0; }

    public function paymentMethodLabel(): ?string
    {
        return $this->payment_method ? (Payment::METHODS[$this->payment_method] ?? ucfirst($this->payment_method)) : null;
    }

    public function visitTimeLabel(): ?string
    {
        return $this->visit_time ? substr($this->visit_time, 0, 5) : null;
    }
}
