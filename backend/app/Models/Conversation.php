<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Conversation extends Model
{
    use HasFactory;

    protected $fillable = [
        'listing_id',
        'guest_id',
        'host_id',
        'last_message_at',
    ];

    protected $casts = [
        'last_message_at' => 'datetime',
    ];

    // Relationships
    public function listing()
    {
        return $this->belongsTo(Listing::class);
    }

    public function guest()
    {
        return $this->belongsTo(User::class, 'guest_id');
    }

    public function host()
    {
        return $this->belongsTo(User::class, 'host_id');
    }

    public function messages()
    {
        return $this->hasMany(Message::class);
    }

    public function latestMessage()
    {
        return $this->hasOne(Message::class)->latest();
    }

    // Scopes
    public function scopeForUser($query, $userId)
    {
        return $query->where('guest_id', $userId)
                    ->orWhere('host_id', $userId);
    }

    public function scopeRecent($query)
    {
        return $query->orderBy('last_message_at', 'desc');
    }

    // Methods
    public function getOtherParticipant($userId)
    {
        return $this->guest_id === $userId ? $this->host : $this->guest;
    }

    public function hasUnreadMessages($userId)
    {
        return $this->messages()
                   ->where('receiver_id', $userId)
                   ->whereNull('read_at')
                   ->exists();
    }

    public function getUnreadCount($userId)
    {
        return $this->messages()
                   ->where('receiver_id', $userId)
                   ->whereNull('read_at')
                   ->count();
    }

    public function markAllAsRead($userId)
    {
        $this->messages()
            ->where('receiver_id', $userId)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
    }
}