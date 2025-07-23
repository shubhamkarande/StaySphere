<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Listing extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'host_id',
        'title',
        'description',
        'property_type',
        'room_type',
        'accommodates',
        'bedrooms',
        'bathrooms',
        'price_per_night',
        'cleaning_fee',
        'service_fee',
        'minimum_nights',
        'maximum_nights',
        'address',
        'city',
        'state',
        'country',
        'postal_code',
        'latitude',
        'longitude',
        'amenities',
        'images',
        'house_rules',
        'cancellation_policy',
        'instant_book',
        'is_active',
        'featured',
    ];

    protected $casts = [
        'amenities' => 'array',
        'images' => 'array',
        'house_rules' => 'array',
        'instant_book' => 'boolean',
        'is_active' => 'boolean',
        'featured' => 'boolean',
        'price_per_night' => 'decimal:2',
        'cleaning_fee' => 'decimal:2',
        'service_fee' => 'decimal:2',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
    ];

    // Relationships
    public function host()
    {
        return $this->belongsTo(User::class, 'host_id');
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function availabilities()
    {
        return $this->hasMany(Availability::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeFeatured($query)
    {
        return $query->where('featured', true);
    }

    public function scopeByLocation($query, $location)
    {
        return $query->where(function ($q) use ($location) {
            $q->where('city', 'ILIKE', "%{$location}%")
              ->orWhere('state', 'ILIKE', "%{$location}%")
              ->orWhere('country', 'ILIKE', "%{$location}%");
        });
    }

    public function scopeByPriceRange($query, $minPrice, $maxPrice)
    {
        if ($minPrice) {
            $query->where('price_per_night', '>=', $minPrice);
        }
        if ($maxPrice) {
            $query->where('price_per_night', '<=', $maxPrice);
        }
        return $query;
    }

    public function scopeByPropertyType($query, $types)
    {
        if (is_string($types)) {
            $types = explode(',', $types);
        }
        return $query->whereIn('property_type', $types);
    }

    public function scopeByRoomType($query, $roomType)
    {
        return $query->where('room_type', $roomType);
    }

    public function scopeByAmenities($query, $amenities)
    {
        if (is_string($amenities)) {
            $amenities = explode(',', $amenities);
        }
        
        foreach ($amenities as $amenity) {
            $query->whereJsonContains('amenities', $amenity);
        }
        
        return $query;
    }

    public function scopeByGuests($query, $guests)
    {
        return $query->where('accommodates', '>=', $guests);
    }

    public function scopeByBedrooms($query, $bedrooms)
    {
        return $query->where('bedrooms', '>=', $bedrooms);
    }

    public function scopeByBathrooms($query, $bathrooms)
    {
        return $query->where('bathrooms', '>=', $bathrooms);
    }

    public function scopeInstantBook($query)
    {
        return $query->where('instant_book', true);
    }

    // Accessors
    public function getAverageRatingAttribute()
    {
        return $this->reviews()->avg('rating') ?: 0;
    }

    public function getTotalReviewsAttribute()
    {
        return $this->reviews()->count();
    }

    public function getPrimaryImageAttribute()
    {
        return $this->images[0] ?? '/placeholder-image.jpg';
    }

    public function getFullAddressAttribute()
    {
        return trim("{$this->address}, {$this->city}, {$this->state} {$this->postal_code}, {$this->country}");
    }

    // Methods
    public function isAvailable($checkIn, $checkOut)
    {
        // Check if there are any conflicting bookings
        $conflictingBookings = $this->bookings()
            ->where('status', '!=', 'cancelled')
            ->where(function ($query) use ($checkIn, $checkOut) {
                $query->whereBetween('check_in', [$checkIn, $checkOut])
                      ->orWhereBetween('check_out', [$checkIn, $checkOut])
                      ->orWhere(function ($q) use ($checkIn, $checkOut) {
                          $q->where('check_in', '<=', $checkIn)
                            ->where('check_out', '>=', $checkOut);
                      });
            })
            ->exists();

        return !$conflictingBookings;
    }

    public function calculateTotalPrice($checkIn, $checkOut, $guests = 1)
    {
        $nights = \Carbon\Carbon::parse($checkIn)->diffInDays(\Carbon\Carbon::parse($checkOut));
        $subtotal = $this->price_per_night * $nights;
        $cleaningFee = $this->cleaning_fee ?: 0;
        $serviceFee = $this->service_fee ?: 0;
        
        return [
            'nights' => $nights,
            'subtotal' => $subtotal,
            'cleaning_fee' => $cleaningFee,
            'service_fee' => $serviceFee,
            'total' => $subtotal + $cleaningFee + $serviceFee,
        ];
    }
}