<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Availability extends Model
{
    use HasFactory;

    protected $fillable = [
        'listing_id',
        'date',
        'is_available',
        'price_override',
        'minimum_nights_override',
    ];

    protected $casts = [
        'date' => 'date',
        'is_available' => 'boolean',
        'price_override' => 'decimal:2',
    ];

    public function listing()
    {
        return $this->belongsTo(Listing::class);
    }
}