<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'avatar',
        'is_host',
        'bio',
        'verified_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'verified_at' => 'datetime',
        'is_host' => 'boolean',
    ];

    // Relationships
    public function listings()
    {
        return $this->hasMany(Listing::class, 'host_id');
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class, 'guest_id');
    }

    public function hostBookings()
    {
        return $this->hasManyThrough(Booking::class, Listing::class, 'host_id', 'listing_id');
    }

    public function reviews()
    {
        return $this->hasMany(Review::class, 'reviewer_id');
    }

    public function receivedReviews()
    {
        return $this->hasManyThrough(Review::class, Listing::class, 'host_id', 'listing_id');
    }

    public function sentMessages()
    {
        return $this->hasMany(Message::class, 'sender_id');
    }

    public function receivedMessages()
    {
        return $this->hasMany(Message::class, 'receiver_id');
    }

    // Scopes
    public function scopeHosts($query)
    {
        return $query->where('is_host', true);
    }

    public function scopeVerified($query)
    {
        return $query->whereNotNull('verified_at');
    }

    // Accessors
    public function getAvatarUrlAttribute()
    {
        return $this->avatar ?: '/default-avatar.jpg';
    }
}