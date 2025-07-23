<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    use HasFactory;

    protected $fillable = [
        'conversation_id',
        'sender_id',
        'receiver_id',
        'message',
        'read_at',
    ];

    protected $casts = [
        'read_at' => 'datetime',
    ];

    // Relationships
    public function conversation()
    {
        return $this->belongsTo(Conversation::class);
    }

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function receiver()
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }

    // Scopes
    public function scopeUnread($query)
    {
        return $query->whereNull('read_at');
    }

    public function scopeRead($query)
    {
        return $query->whereNotNull('read_at');
    }

    // Accessors
    public function getIsReadAttribute()
    {
        return !is_null($this->read_at);
    }

    // Methods
    public function markAsRead()
    {
        $this->update(['read_at' => now()]);
        return $this;
    }
}