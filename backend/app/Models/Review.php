<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    use HasFactory;

    protected $fillable = [
        'listing_id',
        'booking_id',
        'reviewer_id',
        'rating',
        'comment',
        'cleanliness_rating',
        'communication_rating',
        'checkin_rating',
        'accuracy_rating',
        'location_rating',
        'value_rating',
    ];

    protected $casts = [
        'rating' => 'integer',
        'cleanliness_rating' => 'integer',
        'communication_rating' => 'integer',
        'checkin_rating' => 'integer',
        'accuracy_rating' => 'integer',
        'location_rating' => 'integer',
        'value_rating' => 'integer',
    ];

    // Relationships
    public function listing()
    {
        return $this->belongsTo(Listing::class);
    }

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewer_id');
    }

    // Scopes
    public function scopeRecent($query)
    {
        return $query->orderBy('created_at', 'desc');
    }

    public function scopeHighRated($query, $minRating = 4)
    {
        return $query->where('rating', '>=', $minRating);
    }

    // Accessors
    public function getAverageDetailRatingAttribute()
    {
        $ratings = [
            $this->cleanliness_rating,
            $this->communication_rating,
            $this->checkin_rating,
            $this->accuracy_rating,
            $this->location_rating,
            $this->value_rating,
        ];

        $validRatings = array_filter($ratings, fn($rating) => $rating > 0);
        
        return count($validRatings) > 0 ? round(array_sum($validRatings) / count($validRatings), 1) : 0;
    }

    public function getRatingStarsAttribute()
    {
        return str_repeat('★', $this->rating) . str_repeat('☆', 5 - $this->rating);
    }
}