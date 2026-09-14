<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Property extends Model
{
    use HasFactory;

    protected $fillable = [
        'owner_id', 'name', 'address', 'city', 'type', 'monthly_rent', 'visit_fee', 'commission_rate', 'status', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'monthly_rent' => 'decimal:2',
            'visit_fee' => 'decimal:2',
            'commission_rate' => 'decimal:2',
        ];
    }

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function leases()
    {
        return $this->hasMany(Lease::class);
    }

    public function activeLease()
    {
        return $this->hasOne(Lease::class)->where('leases.status', 'active')->latestOfMany();
    }

    public function issues()
    {
        return $this->hasMany(Issue::class);
    }

    public function visitRequests()
    {
        return $this->hasMany(VisitRequest::class);
    }

    public function visitSlots()
    {
        return $this->hasMany(VisitSlot::class);
    }

    // Dates still open for booking, soonest first
    public function upcomingVisitSlots()
    {
        return $this->visitSlots()
            ->where('visit_slots.is_active', true)
            ->whereDate('visit_slots.date', '>=', today())
            ->orderBy('visit_slots.date');
    }

    public function images()
    {
        return $this->hasMany(PropertyImage::class)->orderByDesc('is_primary')->orderBy('id');
    }

    public function mainImage()
    {
        return $this->hasOne(PropertyImage::class)->where('is_primary', true);
    }

    // All payments made on any lease of this property (through Lease)
    public function payments()
    {
        return $this->hasManyThrough(Payment::class, Lease::class);
    }
}
