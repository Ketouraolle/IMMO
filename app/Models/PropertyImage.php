<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PropertyImage extends Model
{
    protected $fillable = ['property_id', 'path', 'is_primary'];

    protected function casts(): array
    {
        return [
            'is_primary' => 'boolean',
        ];
    }

    public function property()
    {
        return $this->belongsTo(Property::class);
    }
}
