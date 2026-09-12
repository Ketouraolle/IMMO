<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Issue extends Model
{
    use HasFactory;

    protected $fillable = [
        'property_id', 'reported_by', 'assigned_to', 'title', 'description', 'priority', 'status',
    ];

    public function property()
    {
        return $this->belongsTo(Property::class);
    }

    public function reportedBy()
    {
        return $this->belongsTo(User::class, 'reported_by');
    }

    public function assignedTo()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }
}
