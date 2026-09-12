<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VisitRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'property_id', 'name', 'email', 'phone', 'message', 'status', 'handled_by',
    ];

    public function property()
    {
        return $this->belongsTo(Property::class);
    }

    public function handledBy()
    {
        return $this->belongsTo(User::class, 'handled_by');
    }
}
